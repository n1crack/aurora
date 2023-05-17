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

        $this->put('hash', $this->hash());
    }

    /**
     * @param  array  $itemConditionsOrder
     */
    public function setItemConditionsOrder(array $itemConditionsOrder): void
    {
        $this->itemConditionsOrder = $itemConditionsOrder;
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

    public function price()
    {
        return $this->price + array_sum(Arr::pluck($this->attributes(), 'price'));
    }

    public function subtotal()
    {
        $subtotal = $this->price() * $this->quantity;

        foreach ($this->conditions() as $condition) {
            $subtotal += $condition->calculate($subtotal);
        }

        return $subtotal;
    }

    public function condition(Condition $condition)
    {
        $this->conditions->put($condition->getName(), $condition);

        return $this;
    }

    public function conditions($type = null)
    {
        $subtotal = $this->price() * $this->quantity;
        $conditions = $this->conditions
            ->sortByOrder($this->getConditionsOrder())
            ->calculateSubTotal($subtotal)
            ->filterType($type);

        return $conditions;
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
}
