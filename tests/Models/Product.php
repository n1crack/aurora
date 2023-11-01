<?php

namespace Ozdemir\Aurora\Tests\Models;

use Ozdemir\Aurora\Contracts\Sellable;
use Ozdemir\Aurora\Traits\SellableTrait;

/** @noinspection */
class Product implements Sellable
{
    use SellableTrait;

    public int $id;
    public float|int $price;
    public float|int $basePrice;
    public int $quantity;
    public float|int $weight;
}
