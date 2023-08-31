<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Ozdemir\Aurora\Storage\StorageInterface;

class Cart implements \Serializable
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
     * @var array
     */
    private array $meta;

    /**
     * @var
     */
    private $dispatcher;

    /**
     * @var array
     */
    private array $config;

    private string $session;

    public function __construct(string $session, StorageInterface $storage, $dispatcher, $config)
    {
        $this->storage = $storage;

        $this->dispatcher = $dispatcher;
        $this->config = array_merge(config('cart'), $config);

        $this->session = $session;

        $this->initCart();
    }

    public function initCart()
    {
        $this->items = $this->getStorage('cart:items') ?? new CartCollection();
        $this->conditions = $this->getStorage('cart:conditions') ?? new ConditionCollection();

        $this->conditionsOrder = $this->getStorage('cart:conditionsOrder') ?? config('cart.condition_order.cart');
        $this->itemConditionsOrder = $this->getStorage('cart:itemConditionsOrder') ?? config('cart.condition_order.item');
        $this->meta = $this->getStorage('cart:meta') ?? [];
    }

    public function clone(string $session = null, StorageInterface $storage = null, $dispatcher = null, $config = null): Cart
    {
        $storage ??= $this->storage;

        $session ??= $this->session;

        return new static($session, $storage, $dispatcher ?? $this->dispatcher, $config ?? $this->config);
    }

    public function self(): static
    {
        return $this;
    }

    public function getInstanceKey()
    {
        return $this->storage->instance;
    }

    public function load(Cart $oldCart): self
    {
        $this->items = $oldCart->items;
        $this->updateItemStorage();

        $this->conditions = $oldCart->conditions;
        $this->updateConditionStorage();

        return $this;
    }

    public function mergeItems(Cart $oldCart): self
    {
        $this->items = $this->items->merge($oldCart->items);
        $this->updateItemStorage();

        return $this;
    }

    public function emit($event, ...$args)
    {
        $this->dispatcher->dispatch($this->getInstanceKey() . '.' . $event, ...$args);
    }

    public function listen($event, $callback)
    {
        $this->dispatcher->listen($this->getInstanceKey() . '.' . $event, $callback());
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
        $cartItem = new $cartItemClass($data, $this->itemConditionsOrder, $this->config);
        $this->items->updateOrAdd($cartItem);

        $this->updateItemStorage();

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

    public function getItemSubTotalBasePrice()
    {
        return $this->items->sum(fn (CartItem $item) => $item->getPriceWithoutConditions() * $item->quantity);
    }

    public function getItemSubTotal()
    {
        return round($this->items->sum(fn (CartItem $item) => $item->subtotal()), $this->config['precision']);
    }

    public function subtotal()
    {
        return round($this->calculateConditions($this->getItemSubTotal(), 'subtotal'), $this->config['precision']);
    }

    public function total()
    {
        return round($this->calculateConditions($this->subtotal(), 'total'), $this->config['precision']);
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
        return $this->items->sum(fn (CartItem $item) => $item->weight() * $item->quantity);
    }

    public function clear()
    {
        $this->emit('clearing');
        $this->items = new CartCollection();
        $this->conditions = new ConditionCollection();

        $this->updateItemStorage();
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
        if (!in_array($condition->getTarget(), ['total', 'subtotal'])) {
            throw new \Exception("The target for the condition can only be a subtotal or price.");
        }
        $this->conditions->put($condition->getName(), $condition);

        $this->updateConditionStorage();

        return $this;
    }

    public function setConditions(ConditionCollection $conditions)
    {
        $this->conditions = $conditions;

        $this->updateConditionStorage();
    }

    public function getConditions()
    {
        return $this->conditions;
    }

    public function updateConditionPrice()
    {
        $this->items()->each->calculateConditionPrices();

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
    }

    public function itemConditions($type = null)
    {
        return Cart::items()
            ->pluck('conditions')
            ->flatten(1)
            ->groupBy('name')
            ->map(function($conditions) {
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
        $this->putStorage('cart:conditionsOrder', $conditionsOrder);

        $this->conditionsOrder = $conditionsOrder;

        $this->updateConditionPrice();
    }

    /**
     * @param string[] $itemConditionsOrder
     */
    public function setItemConditionsOrder(array $itemConditionsOrder): void
    {
        $this->putStorage('cart:itemConditionsOrder', $itemConditionsOrder);

        $this->itemConditionsOrder = $itemConditionsOrder;

        $this->items()->each->setItemConditionsOrder($itemConditionsOrder);
    }

    /**
     * @param string[] $itemConditionsOrder
     */
    public function setMeta(string $key, mixed $data): void
    {
        $this->meta[$key] = $data;

        $this->putStorage('cart:meta', $this->meta);
    }

    public function getMeta(string $key, mixed $default): mixed
    {
        return $this->meta[$key] ?? $default;
    }

    public function updateItemStorage()
    {
        $this->updateConditionPrice();

        $this->putStorage('cart:items', $this->items);
    }

    public function updateConditionStorage()
    {
        $this->updateConditionPrice();

        $this->putStorage('cart:conditions', $this->conditions);
    }

    public static function defaultSessionKey(): string
    {
        if (Auth::check()) {
            return 'user:' . Auth::id();
        }
        $guestToken = Cookie::get('guest_token');

        if (!$guestToken) {
            $guestToken = uniqid();
            Cookie::queue('guest_token', $guestToken, 1440);
        }

        return 'guest:' . $guestToken;
    }

    public function loadSession($key)
    {
        $this->session = $key;

        $this->initCart();
    }

    public function getSessionKey(): string
    {
        return $this->session;
    }

    protected function getStorage(string $key)
    {
        return $this->storage->get($this->getSessionKey() . '.' . $key);
    }

    protected function putStorage(string $key, mixed $data)
    {
        $this->storage->put($this->getSessionKey() . '.' . $key, $data);
    }

    public function serialize()
    {
        return serialize($this->__serialize());
    }

    public function unserialize($string)
    {

        $data = unserialize($string, [CartCollection::class, ConditionCollection::class, StorageInterface::class]);

        $this->__unserialize($data);
    }

    public function __serialize(): array
    {
        return [
            $this->items,
            $this->conditions,
            $this->conditionsOrder,
            $this->itemConditionsOrder,
            $this->storage,
        ];
    }

    public function __unserialize(array $data): void
    {
        [
            $this->items,
            $this->conditions,
            $this->conditionsOrder,
            $this->itemConditionsOrder,
            $this->storage,
        ] = $data;
    }
}
