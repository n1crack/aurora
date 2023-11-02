<?php

namespace Ozdemir\Aurora\Tests\Stubs\Calculators;

use Closure;
use Ozdemir\Aurora\Money;

/* @noinspection */

class Shipping
{
    public function handle($payload, Closure $next)
    {
        /* @var Money $price */
        [$price, $breakdowns] = $payload;

        $shippingCost = new Money(10);

        $price = $price->add($shippingCost);

        $breakdowns[] = ['label' => 'Shipping', 'value' => $shippingCost];

        return $next([$price, $breakdowns]);
    }
}
