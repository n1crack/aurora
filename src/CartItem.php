<?php

namespace Ozdemir\Aurora;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Ozdemir\Aurora\Traits\CollectionArrayAccess;

class CartItem extends Collection
{
    use CollectionArrayAccess;

    protected array $itemConditionsOrder;

    /**
     * @throws Exception
     */
    public function __construct($items, $itemConditionsOrder)
    {
        $this->validate($items);

        $items['attributes'] = $this->setAttributes($items['attributes'] ?? []);
        $items['conditions'] = new ConditionCollection($items['conditions'] ?? []);

        parent::__construct($items);

        $this->setItemConditionsOrder($itemConditionsOrder);
        $this->calculateConditionPrices();


        $this->put('hash', $this->hash());
    }

    /**
     * @param  array  $itemConditionsOrder
     */
    public function setItemConditionsOrder(array $itemConditionsOrder): void
    {
        $this->itemConditionsOrder = $itemConditionsOrder;
        $this->calculateConditionPrices();
    }

    /**
     * @throws Exception
     */
    public function validate($items): void
    {
        $validator = Validator::make($items, [
            'id' => ['required'],
            'name' => ['required'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'attributes' => ['sometimes', 'array'],
            'conditions' => ['sometimes'],
            'weight' => ['sometimes', 'numeric', 'min:0'],
            'sku' => ['sometimes'],
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }
    }

    public function hash()
    {
        $attr = $this->attributes->pluck('value')->join('-');
        $sku = $this->sku;

        return md5($this->id.$attr.$sku);
    }

    public function increaseQuantity(int $value)
    {
        $this->quantity += $value;
    }

    public function setQuantity(int $value)
    {
        $this->quantity = $value;
    }

    public function decreaseQuantity(int $value)
    {
        $this->quantity -= $value;
    }

    public function getPriceWithoutConditions()
    {
        return floatval($this->price + array_sum(Arr::pluck($this->attributes(), 'price')));
    }

    public function price(): float
    {
        return $this->calculateConditions($this->getPriceWithoutConditions(), 'price');
    }

    public function subtotal(): float
    {
        $subtotal = $this->price() * $this->quantity;

        $subtotal = $this->calculateConditions($subtotal, 'subtotal');

        return floatval($subtotal);
    }

    public function condition(Condition $condition)
    {
        if (!in_array($condition->getTarget(), ['price', 'subtotal'])) {
            throw new \Exception("The target for the condition can only be a price or subtotal.");
        }
        $this->conditions->put($condition->getName(), $condition);

        $this->calculateConditionPrices();

        return $this;
    }

    public function conditions($type = null, $target = null)
    {
        return $this->conditions
            ->sortByOrder($this->getConditionsOrder())
            ->filterType($type)
            ->filterTarget($target);
    }

    public function weight()
    {
        return $this->weight ?? 0;
    }

    public function attributes()
    {
        return $this->attributes ?? [];
    }

    public function update($data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * @return string[]
     */
    public function getConditionsOrder(): array
    {
        return $this->itemConditionsOrder;
    }

    private function setAttributes($attributes)
    {
        return collect($attributes)->map(fn ($item) => new CartAttribute($item));
    }

    /**
     * @param float|int $subtotal
     * @return float|int
     */
    public function calculateConditions(float|int $subtotal, string $target): int|float
    {
        foreach ($this->conditions(target: $target) as $condition) {
            $subtotal += $condition->value;
        }

        return $subtotal;
    }

    /**
     * @return void
     */
    public function calculateConditionPrices(): void
    {
        $this->conditions(target: 'price')->calculateSubTotal($this->getPriceWithoutConditions());
        $this->conditions(target: 'subtotal')->calculateSubTotal($this->price() * $this->quantity);
    }
}
