<?php

namespace Ozdemir\Aurora;

class CartItemOption
{
    public float|int $price;
    public float|int $weight = 0;
    public bool $percent = false;

    public function __construct(public string $label, public string $value)
    {
    }

    public function setPrice(float|int $price, $percent = false): void
    {
        $this->price = $price;
        $this->percent = $percent;
    }

    public function getPrice(float|int $cartItemPrice): float|int
    {
        if (!$this->percent) {
            return $this->price;
        }

        return $cartItemPrice * $this->price / 100;
    }

    public function setWeight(float|int $weight): void
    {
        $this->weight = $weight;
    }
}
