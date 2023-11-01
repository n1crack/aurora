<?php

namespace Ozdemir\Aurora;

use Ozdemir\Aurora\Contracts\CartItemInterface;
use Ozdemir\Aurora\Contracts\CartStorage;
use Ozdemir\Aurora\Enums\CartCalculator;
use Ozdemir\Aurora\Enums\CartItemCalculator;

class Cart
{
    private string $sessionKey;

    private CartCalculatorCollection $pipeline;

    public CartItemCollection $items;

    public function __construct(readonly public CartStorage $storage)
    {
        $storage_session_key = config('cart.storage.session_key');

        $this->sessionKey = (new $storage_session_key)();

        $this->pipeline = app(CartCalculatorCollection::class);

        $this->load();
    }

    public function make(CartStorage $storage): static
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
        $this->save();
    }

    public function sync(CartItemInterface ...$cartItems): void
    {
        $this->clear();

        $this->add(...$cartItems);

        $this->save();
    }

    public function item(string $hash): CartItemInterface
    {
        // todo: throw NotFoundException
        return $this->items->get($hash);
    }

    public function update(string $hash, int $quantity): ?CartItemInterface
    {
        return tap($this->item($hash), function(CartItemInterface $cartItem) use ($quantity) {
            $cartItem->quantity = $quantity;

            $this->save();
        });
    }

    public function subtotal(): Money
    {
        [$subtotal, $breakdowns] = Calculator::calculate(
            $this->items->subtotal(),
            $this->pipeline[CartCalculator::SUBTOTAL->value] ?? []
        );

        return $subtotal->setBreakdowns($breakdowns)->round();
    }

    public function total(): Money
    {
        [$total, $breakdowns] = Calculator::calculate(
            $this->subtotal(),
            $this->pipeline[CartCalculator::TOTAL->value] ?? []
        );

        return $total->setBreakdowns($breakdowns)->round();
    }

    public function weight(): float|int
    {
        return $this->items->reduce(fn ($total, CartItemInterface $cartItem) => $total + $cartItem->weight(), 0);
    }

    public function remove($hash): void
    {
        $this->items->forget($hash);

        $this->save();
    }

    public function clear(): void
    {
        $this->items = new CartItemCollection();

        $this->save();
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

    public function count(): int
    {
        return $this->items->count();
    }

    public function loadSession($sessionKey): void
    {
        $this->sessionKey = $sessionKey;

        $this->load();
    }

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    private function save(): void
    {
        $this->putStorage('items', $this->items);
    }

    private function load(): void
    {
        $this->items = $this->getStorage('items') ?? new CartItemCollection();

        $this->pipeline = $this->pipeline->reload($this->getStorage('pipeline'));
    }

    protected function putStorage(string $key, mixed $data): void
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

    public function rollback(string $string): static
    {
        $cart = unserialize($string);

        $this->items = $cart->items();

        $this->pipeline = $this->pipeline->reload($cart->calculators());

        return $this;
    }

    public function calculators(): CartCalculatorCollection
    {
        return $this->pipeline;
    }

    public function calculateUsing(CartCalculator $target, array $pipeline): void
    {
        $this->pipeline[$target->value] = $pipeline;

        $this->putStorage('pipeline', $this->pipeline);
    }

    public function calculateItemsUsing(CartItemCalculator $target, array $pipeline): void
    {
        $this->pipeline[$target->value] = $pipeline;

        $this->putStorage('pipeline', $this->pipeline);
        $this->putStorage('items', $this->items);
    }

    public function instance(): array
    {
        return [
            'subtotal' => $this->subtotal(),
            'total' => $this->total(),
            'item_breakdowns' => [],
            'cart_breakdowns' => [],
            'meta' => [],
        ];
    }
}
