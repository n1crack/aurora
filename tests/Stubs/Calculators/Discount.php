<?php

namespace Ozdemir\Aurora\Tests\Stubs\Calculators;

use Closure;
use Ozdemir\Aurora\Money;

/* @noinspection */

class Discount
{
    public function handle($payload, Closure $next)
    {
        /* @var Money $price */
        [$price, $breakdowns] = $payload;

        //        $total = \Ozdemir\Aurora\Calculator::skip($this, function() {
        //            return \Ozdemir\Aurora\Facades\Cart::total()->amount();
        //        });
        //
        //        dump($total);

        $discountPrice = new Money($price->multiply(5 / 100)->amount());

        $price = $price->subtract($discountPrice);

        $breakdowns[] = ['label' => 'Discount', 'value' => $discountPrice->multiply(-1)];

        return $next([$price, $breakdowns]);
    }
}
