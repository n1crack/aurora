<?php

namespace Ozdemir\Aurora\Tests\Stubs\Calculators;

use Closure;
use Ozdemir\Aurora\Money;

/* @noinspection */

class Tax
{
    public function handle($payload, Closure $next)
    {
        /* @var Money $price */
        [$price, $breakdowns] = $payload;

        $taxPrice = new Money(15);

        $price = $price->add($taxPrice);

        $breakdowns[] = ['label' => 'Tax', 'value' => $taxPrice];

        return $next([$price, $breakdowns]);
    }
}
