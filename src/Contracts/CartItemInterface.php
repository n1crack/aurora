<?php

namespace Ozdemir\Aurora\Contracts;

interface CartItemInterface
{
    public function __construct(Sellable $model, int $quantity, bool $gift = false);

    public function hash();

    public function withOption(string $name, mixed $value, float|int $price = 0, bool $percent = false, float|int $weight = 0);

    public function withMeta(string $name, mixed $value);

    public function increaseQuantity(int $value);

    public function setQuantity(int $value);

    public function decreaseQuantity(int $value);

    public function weight();

    public function optionPrice();

    public function unitPrice();

    public function subtotal();
}
