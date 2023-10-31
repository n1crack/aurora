<?php

namespace Ozdemir\Aurora;

use Ozdemir\Aurora\Contracts\CartItemInterface;
use Ozdemir\Aurora\Storages\StorageInterface;

class Cart
{
    public CartItemCollection $items;

    private string $sessionKey;

    public function __construct(readonly public StorageInterface $storage)
    {
        $storage_session_key = config('cart.storage.session_key');

        $this->sessionKey = (new $storage_session_key)();

        $this->items = $this->getStorage('items') ?? new CartItemCollection();
    }

    public function make(StorageInterface $storage): static
    {
        return new static($storage);
    }

    public function items(): CartItemCollection
    {
        return $this->items;
    }

    public function add(CartItemInterface  ...$cartItems): void
    {
        foreach ($cartItems as $cartItem) {
            $this->items->updateOrAdd($cartItem);
        }
        $this->updateStorage('items', $this->items);
    }

    public function sync(CartItemInterface ...$cartItems): void
    {
        $this->clear();

        $this->add(...$cartItems);
        $this->updateStorage('items', $this->items);
    }

    public function item(string $hash): CartItemInterface
    {
        // todo: throw NotFound exception
        return $this->items->get($hash);
    }

    public function update(string $hash, int $quantity): ?CartItemInterface
    {
        return tap($this->item($hash), function(CartItemInterface $cartItem) use ($quantity) {
            $cartItem->quantity = $quantity;
            $this->updateStorage('items', $this->items);
        });
    }

    public function subtotal(): Money
    {
        return $this->items->subtotal();
    }

    public function total(): Money
    {
        return $this->subtotal()->round();
    }

    public function weight(): float|int
    {
        return $this->items->reduce(fn ($total, CartItemInterface $cartItem) => $total + $cartItem->weight(), 0);
    }

    public function remove($hash): void
    {
        $this->items->forget($hash);
        $this->updateStorage('items', $this->items);
    }

    public function clear(): void
    {
        $this->items = new CartItemCollection();
        $this->updateStorage('items', $this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    public function getInstanceKey(): string
    {
        return $this->storage->instance;
    }

    public function quantity(): int
    {
        return $this->items->sum('quantity');
    }

    public function loadSession($sessionKey)
    {
        $this->sessionKey = $sessionKey;

        $this->items = $this->getStorage('items') ?? new CartItemCollection();
    }

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    protected function updateStorage(string $key, mixed $data): void
    {
        $this->storage->put($this->getSessionKey() . '.' . $key, $data);
    }

    protected function getStorage(string $key): mixed
    {
        return $this->storage->get($this->getSessionKey() . '.' . $key);
    }

    public function snapshot(): string
    {
        return serialize($this);
    }

    public function restore(string $string): static
    {
        $cart = unserialize($string);

        $this->items = $cart->items();

        return $this;
    }
}
