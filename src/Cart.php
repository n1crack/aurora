<?php

namespace Ozdemir\Aurora;

use Carbon\Traits\Serialization;
use Laravel\SerializableClosure\SerializableClosure;
use Ozdemir\Aurora\Storage\StorageInterface;
use Symfony\Component\VarDumper\VarDumper;

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
    protected array $conditionsOrder;

    /**
     * @var array|mixed|string[]
     */
    protected array $itemConditionsOrder;

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

        $this->conditionsOrder = $this->storage->get('cart:conditionsOrder') ?? config('cart.condition_order.cart');
        $this->itemConditionsOrder = $this->storage->get('cart:itemConditionsOrder') ?? config('cart.condition_order.item');
    }

    public function clone(StorageInterface $storage, $dispatcher = null, $config = null): Cart
    {
        return new self($storage, $dispatcher ?? $this->dispatcher, $config ?? $this->config);
    }

    public function self(): static
    {
        return $this;
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
            $added = [];
            foreach ($data as $item) {
                $added[] = $this->add($item);
            }

            return $added;
        }

        $cartItemClass = $this->config['cart_item'];
        $cartItem = new $cartItemClass($data, $this->itemConditionsOrder);
        $this->items->updateOrAdd($cartItem);

        $this->updateItemStorage();
        $this->updateConditionPrice();

        $this->emit('added');

        return $this->item($cartItem->hash());
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
            throw new \Exception('Item has not found..');
        }

        if (is_int($data)) {
            if ($data < 1) {
                throw new \Exception('Negative or zero value not allowed.');
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

    public function getItemSubTotal()
    {
        return $this->items->sum(fn($item) => $item->subtotal());
    }

    public function subtotal()
    {
        return floatval($this->calculateConditions($this->getItemSubTotal(), 'subtotal'));
    }

    public function total()
    {
        return floatval($this->calculateConditions($this->subtotal(), 'total'));
    }

    /**
     * @param mixed $subtotal
     * @return mixed
     */
    public function calculateConditions(mixed $subtotal, string $target): mixed
    {
        if ($this->quantity()) {
            foreach ($this->conditions(target: $target) as $condition) {
                $subtotal += $condition->value;
            }
        }

        return $subtotal;
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
        $this->updateConditionPrice();

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
        if (!in_array($condition->getTarget(), ['total', 'subtotal'])) {
            throw new \Exception("The target for the condition can only be a subtotal or price.");
        }
        $this->conditions->put($condition->getName(), $condition);
        $this->updateConditionStorage();
        $this->updateConditionPrice();

        return $this;
    }

    public function updateConditionPrice()
    {
        $this->conditions(target: 'subtotal')->calculateSubTotal($this->getItemSubTotal());
        $this->conditions(target: 'total')->calculateSubTotal($this->subtotal());
    }

    public function hasCondition($name)
    {
        return $this->conditions->has($name);
    }

    public function removeCondition($name)
    {
        $this->conditions->pull($name);
        $this->updateConditionStorage();
        $this->updateConditionPrice();
    }

    public function itemConditions($type = null): ConditionCollection
    {
        return Cart::items()
            ->pluck('conditions')
            ->flatten()
            ->groupBy('name')
            ->map(function ($conditions) {
                $condition = $conditions->first();
                $condition->value = $conditions->sum('value');

                return $condition;
            })
            ->pipeInto(ConditionCollection::class)
            ->filterType($type);
    }

    public function conditions($type = null, $target = null): ConditionCollection
    {
        return $this->conditions
            ->sortByOrder($this->getConditionsOrder())
            ->filterType($type)
            ->filterTarget($target);
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
     * @param string[] $conditionsOrder
     */
    public function setConditionsOrder(array $conditionsOrder): void
    {
        $this->storage->put('cart:conditionsOrder', $conditionsOrder);
        $this->conditionsOrder = $conditionsOrder;

        $this->updateConditionPrice();
    }

    /**
     * @param string[] $itemConditionsOrder
     */
    public function setItemConditionsOrder(array $itemConditionsOrder): void
    {
        $this->storage->put('cart:itemConditionsOrder', $itemConditionsOrder);

        $this->itemConditionsOrder = $itemConditionsOrder;

        $this->items()->each->setItemConditionsOrder($itemConditionsOrder);
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
