<?php

namespace Ozdemir\Aurora;

use Ozdemir\Aurora\Storage\StorageInterface;

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

    /**
     * @var array|mixed|string[]
     */
    protected array $conditionsOrder = ['discount', 'other', 'tax', 'shipping'];

    /**
     * @var array|mixed|string[]
     */
    protected array $itemConditionsOrder = ['discount', 'other', 'tax', 'shipping'];

    /**
     * @var StorageInterface
     */
    private StorageInterface $storage;

    /**
     * @var
     */
    private $dispatcher;

    /**
     * @var array
     */
    private array $config;

    public function __construct(StorageInterface $storage, $dispatcher, $config)
    {
        $this->storage = $storage;
        $this->dispatcher = $dispatcher;
        $this->config = array_merge(config('cart'), $config);

        $this->items = $this->storage->get('cart:items') ?? new CartCollection();
        $this->conditions = $this->storage->get('cart:conditions') ?? new ConditionCollection();

        $this->conditionsOrder = $this->storage->get('cart:conditionsOrder') ?? $this->conditionsOrder;
        $this->itemConditionsOrder = $this->storage->get('cart:itemConditionsOrder') ?? $this->itemConditionsOrder;
    }

    public function instance(StorageInterface $storage, $dispatcher = null, $config = null)
    {
        return new self($storage, $dispatcher ?? $this->dispatcher, $config ?? $this->config);
    }

    public function getInstanceKey()
    {
        return $this->storage->instance;
    }

    public function emit($event, ...$args)
    {
        $this->dispatcher->dispatch($this->getInstanceKey().'.'.$event, ...$args);
    }

    public function listen($event, $callback)
    {
        $this->dispatcher->listen($this->getInstanceKey().'.'.$event, $callback());
    }

    public function add($data)
    {
        $this->emit('adding');

        if (is_array(head($data))) {
            foreach ($data as $item) {
                $this->add($item);
            }

            return $this;
        }

        $this->items->updateOrAdd(new CartItem($data, $this->itemConditionsOrder));

        $this->updateItemStorage();

        $this->emit('added');

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function update($key, $data = [])
    {
        $this->emit('updating');

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

        $this->updateItemStorage();
        $this->emit('updated');
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
        $this->emit('removing');

        $this->items->forget($key);

        $this->updateItemStorage();
        $this->emit('removed');

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
        $this->emit('clearing');
        $this->items = new CartCollection();
        $this->updateItemStorage();

        $this->conditions = new ConditionCollection();
        $this->updateConditionStorage();
        $this->emit('cleared');

        return $this;
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
        $this->updateConditionStorage();
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
    public function setItemConditionsOrder(array $itemConditionsOrder, $updateExisting = true): void
    {
        // todo : validation..
        $this->storage->put('cart:itemConditionsOrder', $itemConditionsOrder);

        $this->itemConditionsOrder = $itemConditionsOrder;

        if ($updateExisting) {
            $this->items()->each->setItemConditionsOrder($itemConditionsOrder);
        }
    }

    private function updateItemStorage()
    {
        $this->storage->put('cart:items', $this->items);
    }

    private function updateConditionStorage()
    {
        $this->storage->put('cart:conditions', $this->conditions);
    }

    public function serialize()
    {
        return serialize([
            $this->items,
            $this->conditions,
            $this->conditionsOrder,
            $this->itemConditionsOrder,
            $this->storage,
        ]);
    }

    public function unserialize($string)
    {
        [
            $this->items,
            $this->conditions,
            $this->conditionsOrder,
            $this->itemConditionsOrder,
            $this->storage,
        ] = unserialize($string, [CartCollection::class, ConditionCollection::class, StorageInterface::class]);

        return $this;
    }
}
