<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Ozdemir\Aurora\Traits\CollectionArrayAccess;

class Condition extends Collection
{
    use CollectionArrayAccess;

    private $actions = [];

    /**
     * @param $props
     * @throws \Exception
     */
    public function __construct($items = [])
    {
        $validator = Validator::make($items, [
            'name' => ['required'],
            'type' => ['required', 'string', Rule::in(config('cart.condition_order.cart'))],
            'target' => ['required', 'string', Rule::in('price', 'subtotal', 'total')],
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        parent::__construct($items);
    }

    public function setActions($actions): void
    {
        $this->actions = $actions;
    }

    public function getValue(): float
    {
        return $this->value ?? 0;
    }

    /**
     * @return mixed
     */
    public function getName(): mixed
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getType(): mixed
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getTarget(): mixed
    {
        return $this->target;
    }

    public function calculate($subtotal)
    {
        return $this->calculateActionValue($subtotal, 0);
    }

    public function checkActionRule($rule)
    {
        if ($rule === null || $rule === [] || $rule === '') {
            return true;
        }

        if (is_callable($rule)) {
            return $rule();
        }

        if (is_bool($rule)) {
            return $rule;
        }
    }

    public function getActionValue($subtotal, $actionVal)
    {
        if ($subtotal <= 0) {
            return 0;
        }

        if (is_callable($actionVal)) {
            return $actionVal();
        }

        $percentage = Str::endsWith($actionVal, '%');
        $negative = Str::startsWith($actionVal, '-');

        $value = trim($actionVal, " +-%");

        return $value * ($negative ? -1 : 1) * ($percentage ? $subtotal * 0.01 : 1);
    }

    /**
     * @param $subtotal
     * @param float|int $value
     * @return float|int
     */
    public function calculateActionValue($subtotal, float|int $value): int|float
    {
        $actions = is_array(head($this->actions)) ? $this->actions : [$this->actions];

        foreach ($actions as $action) {
            $actionVal = $action['value'];

            $rule = $this->checkActionRule($action['rules'] ?? null);

            if ($rule) {
                $value += $this->getActionValue($subtotal, $actionVal);
            }
        }

        return $value;
    }
}
