<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\Collection;
use Ozdemir\Aurora\Contracts\Sellable;
use Ozdemir\Aurora\Contracts\CartItemInterface;

class CartItem implements CartItemInterface
{
    public string $hash;

    public Collection $options;

    public Collection $meta;

    public function __construct(public Sellable $model, public int $quantity, public bool $gift = false)
    {
        $this->options = new Collection();

        $this->meta = new Collection();
    }

    public function hash(): string
    {
        $attr = $this->options->pluck('value')->join('-');

        return $this->hash = md5($this->model->cartItemId() . $attr . $this->gift);
    }

    public function withOption(string $name, mixed $value, float|int $price = 0, bool $percent = false, float|int $weight = 0): static
    {
        $option = new CartItemOption($name, $value);

        $option->setPrice($price, $percent);

        $option->setWeight($weight);

        $this->options->push($option);

        return $this;
    }

    public function withMeta(string $name, mixed $value): static
    {
        $this->meta->push(new CartItemOption($name, $value));

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
        return $this->model->cartItemWeight() * $this->quantity;
    }

    public function itemPrice(): Money
    {
        return new Money($this->model->cartItemPrice());
    }

    public function optionPrice(): Money
    {
        return new Money($this->options->sum(fn (CartItemOption $option) => $option->getPrice($this->itemPrice()->amount())));
    }

    public function unitPrice(): Money
    {
        return $this->itemPrice()->add($this->optionPrice())->round();
    }

    public function subtotal(): Money
    {
        return $this->unitPrice()->multiply($this->quantity);
    }

    public function total(): Money
    {
        return $this->subtotal();
    }
}
