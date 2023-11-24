<?php

namespace Ozdemir\Aurora\Tests\Stubs\Calculators;

use Closure;

/* @noinspection */

class Shipping
{
    public function handle($payload, Closure $next)
    {
        [$price, $breakdowns] = $payload;

        $shippingCost = 10;

        $price = $price + $shippingCost;

        $breakdowns[] = ['label' => 'Shipping', 'value' => $shippingCost];

        return $next([$price, $breakdowns]);
    }
}
