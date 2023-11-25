<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\Collection;
use Ozdemir\Aurora\Contracts\CartItemInterface;
use Ozdemir\Aurora\Contracts\CartStorage;
use Ozdemir\Aurora\Traits\RoundingTrait;

class Cart
{
    use RoundingTrait;

    private string $sessionKey;

    private Collection $calculators;

    public CartItemCollection $items;

    private MetaCollection $meta;

    public function __construct(readonly public CartStorage $storage)
    {
        $this->sessionKey = call_user_func(new (config('cart.session_key_generator')));

        $this->calculators = app(Calculator::class)->calculators();

        $this->load();
    }

    public function make(CartStorage $storage): static
    {
        return new static($storage);
    }

    public function clone(): Cart
    {
        return tap($this->make($this->storage), fn (Cart $cart) => $cart->loadSession($this->getSessionKey()));
    }

    public function items(): CartItemCollection
    {
        return $this->items;
    }

    public function add(CartItemInterface ...$cartItems): void
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

    public function subtotal(): float
    {
        return $this->round($this->items->subtotal());
    }

    public function calculate(): CalculationResult
    {
        [$total, $breakdowns] = resolve(Calculator::class)->calculate(
            $this->subtotal(),
            $this->calculators ?? new Collection()
        );
        return new CalculationResult($total, $breakdowns);
    }

    public function total(): float
    {
        return $this->calculate()->total();
    }

    public function breakdowns()
    {
        return $this->calculate()->breakdowns();
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
        $this->putStorage('meta', $this->meta);
    }

    private function load(): void
    {
        $this->items = $this->getStorage('items', new CartItemCollection());

        $this->meta = $this->getStorage('meta', new MetaCollection());
    }

    protected function putStorage(string $key, mixed $data): void
    {
        $this->storage->put($this->getSessionKey() . '.' . $key, $data);
    }

    protected function getStorage(string $key, $default = null): mixed
    {
        return $this->storage->get($this->getSessionKey() . '.' . $key, $default);
    }

    public function meta(): MetaCollection
    {
        return $this->meta;
    }

    public function setMeta($name, $value): void
    {
        $this->meta->put($name, $value);

        $this->save();
    }

    public function removeMeta($name): void
    {
        $this->meta->forget($name);

        $this->save();
    }

    public function snapshot(): string
    {
        return serialize($this);
    }

    public function rollback(string $string): static
    {
        $cart = unserialize($string);

        $this->items = $cart->items();

        $this->meta = $cart->meta();

        return $this;
    }

    public function calculators(): Collection
    {
        return $this->calculators;
    }

    public function calculateTotalUsing(array $calculators): void
    {
        $this->calculators = new Collection($calculators);
    }

    public function instance(): array
    {
        return [
            // subtotal' => $this->subtotal(),
            // total' => $this->total(),
            // todo.. breakdowns etc..
        ];
    }

    public function validate($clientChecksum): bool
    {
        return $this->checksum() === $clientChecksum;
    }

    public function checksum($withTotal = true): string
    {
        return call_user_func(new (config('cart.checksum_generator')), $withTotal);
    }
}
