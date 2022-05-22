<?php

namespace Ozdemir\Cart;

class Cart
{
    /**
     * @var CartCollection
     */
    protected CartCollection $items;

    /**
     * @var ConditionCollection
     */
    protected ConditionCollection $conditions;

    protected $conditionsOrder = ['discount', 'other', 'tax', 'shipping'];

    protected $itemConditionsOrder = ['discount', 'other', 'tax', 'shipping'];

    private $storage;
    private $config;
    private $dispatcher;

    public function __construct($storage, $dispatcher, $config)
    {
        $this->storage = $storage;
        $this->dispatcher = $dispatcher;
        $this->config = $config;

        $this->items = $this->storage->get('cart:items') ?? new CartCollection();

        $this->conditions = $this->storage->get('cart:conditions') ?? new ConditionCollection();

        $this->conditionsOrder = $this->storage->get('cart:conditionsOrder') ?? $this->conditionsOrder;
        $this->itemConditionsOrder = $this->storage->get('cart:itemConditionsOrder') ?? $this->itemConditionsOrder;
    }

    public function add($data)
    {
        if (is_array(head($data))) {
            foreach ($data as $item) {
                $this->add($item);
            }

            return $this;
        }

        $this->items->updateOrAdd(new CartItem($data, $this->itemConditionsOrder));

        $this->updateItemStorage();

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function update($key, $data = [])
    {
        if (is_array($key)) {
            foreach ($key as $k => $d) {
                $this->update($k, $d);
            }

            return;
        }

        if (!$this->items->has($key)) {
            throw new \Exception('item yok..');
        }

        if (is_int($data)) {
            if ($data < 1) {
                throw new \Exception('negative veya sıfır değer girildi.');
            }
            // increase quantity
            $this->items->get($key)->setQuantity($data);
        } else {
            // update values
            $this->items->get($key)->update($data);
        }
    }

    /**
     * @return CartCollection
     */
    public function items(): CartCollection
    {
        return $this->items;
    }

    public function exists($key)
    {
        return $this->items->has($key);
    }

    public function item($key)
    {
        return $this->items->get($key);
    }

    public function remove($key)
    {
        $this->items->forget($key);

        return $this;
    }

    public function isEmpty()
    {
        return $this->items->isEmpty();
    }

    public function total()
    {
        $subtotal = $this->subtotal();
        if ($this->quantity()) {
            foreach ($this->conditions() as $condition) {
                $subtotal += $condition->calculate($subtotal);
            }
        }

        return $subtotal;
    }

    public function subtotal()
    {
        return $this->items->sum(fn($item) => $item->subtotal());
    }

    public function quantity()
    {
        return $this->items->sum('quantity');
    }

    public function weight()
    {
        return $this->items->sum(fn($item) => $item->weight() * $item->quantity);
    }

    public function clear()
    {
        return $this->items = new CartCollection();
    }

    public function sync($data)
    {
        $this->clear();

        $this->add($data);

        return $this;
    }

    public function condition(Condition $condition)
    {
        $this->conditions->put($condition->getName(), $condition);
        $this->updateConditionStorage();

        return $this;
    }

    public function hasCondition($name)
    {
        return $this->conditions->has($name);
    }

    public function removeCondition($name)
    {
        $this->conditions->pull($name);
    }

    public function itemConditions($type = null): ConditionCollection
    {
        return Cart::items()
            ->pluck('conditions')
            ->flatten()
            ->groupBy('name')
            ->map(function($conditions) {
                $condition = $conditions->first();
                $condition->value = $conditions->sum('value');

                return $condition;
            })
            ->pipeInto(ConditionCollection::class)
            ->filterType($type);

    }

    public function conditions($type = null): ConditionCollection
    {
        return $this->conditions
            ->sortByOrder($this->getConditionsOrder())
            ->calculateSubTotal($this->subtotal())
            ->filterType($type);
    }

    /**
     * @return string[]
     */
    public function getConditionsOrder(): array
    {
        return $this->conditionsOrder;
    }

    /**
     * @return string[]
     */
    public function getItemConditionsOrder(): array
    {
        return $this->itemConditionsOrder;
    }

    /**
     * @param  string[]  $conditionsOrder
     */
    public function setConditionsOrder(array $conditionsOrder): void
    {
        // todo : validation..
        $this->storage->put('cart:conditionsOrder', $conditionsOrder);

        $this->conditionsOrder = $conditionsOrder;
    }

    /**
     * @param  string[]  $itemConditionsOrder
     */
    public function setItemConditionsOrder(array $itemConditionsOrder): void
    {
        // todo : validation..
        $this->storage->put('cart:itemConditionsOrder', $itemConditionsOrder);

        $this->itemConditionsOrder = $itemConditionsOrder;
    }

    private function updateItemStorage()
    {
        $this->storage->put('cart:items', $this->items);
    }

    private function updateConditionStorage()
    {
        $this->storage->put('cart:conditions', $this->conditions);
    }
}
