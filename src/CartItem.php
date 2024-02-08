<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\Collection;
use Ozdemir\Aurora\Contracts\CartItemInterface;
use Ozdemir\Aurora\Contracts\Sellable;
use Ozdemir\Aurora\Traits\RoundingTrait;

class CartItem implements CartItemInterface
{
    use RoundingTrait;

    public string $hash;

    public Collection $options;

    public Collection $meta;

    public function __construct(public Sellable $product, public int $quantity, public bool $gift = false)
    {
        $this->options = new Collection();

        $this->meta = new Collection();
    }

    public function hash(): string
    {
        $options = $this->options->pluck('value')->join('-');

        return $this->hash = md5($this->product->cartItemId() . $options . $this->gift);
    }

    public function withOption(string $name, mixed $value, float|int $price = 0, bool $percent = false, float|int $weight = 0): static
    {
        $option = new CartItemOption($name, $value);

        $option->setPrice($price, $percent);

        $option->setWeight($weight);

        $this->options->put($name, $option);

        return $this;
    }

    public function option(string $name): CartItemOption
    {
        return $this->options->get($name);
    }

    public function withMeta(string $name, mixed $value): static
    {
        $this->meta->put($name, $value);

        return $this;
    }

    public function increaseQuantity(int $value): void
    {
        $this->quantity += $value;
    }

    public function setQuantity(int $value): void
    {
        $this->quantity = $value;
    }

    public function decreaseQuantity(int $value): void
    {
        $this->quantity -= $value;
    }

    public function weight(): float|int
    {
        return $this->product->cartItemWeight() * $this->quantity;
    }

    public function itemPrice(): float|int
    {
        return $this->product->cartItemPrice();
    }

    public function optionPrice(): float|int
    {
        return $this->round($this->options->sum(fn(CartItemOption $option) => $option->getPrice($this->itemPrice())));
    }

    public function unitPrice(): float|int
    {
        return $this->round($this->itemPrice() + $this->optionPrice());
    }

    public function subtotal(): float|int
    {
        return $this->unitPrice() * $this->quantity;
    }
}
