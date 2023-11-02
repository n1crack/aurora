<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\Collection;
use Ozdemir\Aurora\Contracts\CartItemInterface;
use Ozdemir\Aurora\Contracts\Sellable;
use Ozdemir\Aurora\Enums\CartItemCalculator;

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
        $calculatorArray = app(Calculator::class)->pipeline;

        $subtotalCalculators = $calculatorArray[CartItemCalculator::SUBTOTAL->value] ?? [];

        if (array_is_list($subtotalCalculators)) {
            $pipeline = $subtotalCalculators;
        } else {
            $calculators = collect($subtotalCalculators)->filter(fn ($values) => in_array($this->model->id, $values));
            $pipeline = $calculators->keys()->toArray();
        }

        $subtotal = $this->unitPrice()->multiply($this->quantity);

        if (count($pipeline)) {
            [$subtotal, $breakdowns] = Calculator::calculate(
                $this->unitPrice()->multiply($this->quantity),
                $pipeline
            );

            return $subtotal->setBreakdowns($breakdowns)->round();
        }

        return $subtotal->round();
    }
}
