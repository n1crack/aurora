<?php

namespace Ozdemir\Aurora\Tests\Stubs\Calculators;

use Closure;

/* @noinspection */

class Tax
{
    public function handle($payload, Closure $next)
    {
        [$price, $breakdowns] = $payload;

        $taxPrice = 15;

        $price = $price + $taxPrice;

        $breakdowns[] = ['label' => 'Tax', 'value' => $taxPrice];

        return $next([$price, $breakdowns]);
    }
}
