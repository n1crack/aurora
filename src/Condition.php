<?php

namespace Ozdemir\Cart;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;

class Condition
{
    public string $name;
    public string $target;
    public string $type;
    public array $actions;
    public float $value;

    /**
     * @param $props
     * @throws \Exception
     */
    public function __construct($props)
    {
        $validator = Validator::make($props, [
            'name' => ['required', 'string'],
            'type' => ['required', 'string', 'in:tax,discount,shipping,other'],
            'target' => ['required', 'string', 'in:subtotal,total'],
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $this->name = $props['name'];
        $this->type = $props['type'];
        $this->target = $props['target'];
    }

    public function setActions($actions): void
    {
        $this->actions = $actions;
    }

    public function getValue(): float
    {
        return $this->value;
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
        $value = 0;
        if (is_array(head($this->actions))) {
            foreach ($this->actions as $action) {
                $actionVal = $action['value'];
                $rule = $this->checkActionRule($action['rules'] ?? null);
                if ($rule) {
                    $value += $this->getActionValue($subtotal, $actionVal);
                }
            }
        } else {
            $actionVal = $this->actions['value'];

            $rule = $this->checkActionRule($this->actions['rules'] ?? null);
            if ($rule) {
                $value += $this->getActionValue($subtotal, $actionVal);
            }
        }

        return $value;
    }

    public function checkActionRule($rule)
    {
        if ($rule === null || $rule === [] || $rule === '') {
            return true;
        }

        if (is_callable($rule)) {
            return $rule();
        }

        if (is_string($rule) && is_callable(unserialize($rule, [SerializableClosure::class]))) {
            return unserialize($rule, [SerializableClosure::class])();
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

        $percentage = Str::endsWith($actionVal, '%');
        $negative = Str::startsWith($actionVal, '-');

        $value = trim($actionVal, " +-%");

        return $value * ($negative ? -1 : 1) * ($percentage ? $subtotal * 0.01 : 1);
    }
}
