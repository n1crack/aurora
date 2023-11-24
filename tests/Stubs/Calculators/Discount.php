<?php

namespace Ozdemir\Aurora\Tests\Stubs\Calculators;

use Closure;

/* @noinspection */

class Discount
{
    public function handle($payload, Closure $next)
    {
        [$price, $breakdowns] = $payload;

        //        $total = \Ozdemir\Aurora\Calculator::skip($this, function() {
        //            return \Ozdemir\Aurora\Facades\Cart::total();
        //        });
        //
        //        dump($total);

        $discountPrice = $price * 5 / 100;

        $price = $price->subtract($discountPrice);

        $breakdowns[] = ['label' => 'Discount', 'value' => -1 * $discountPrice];

        return $next([$price, $breakdowns]);
    }
}
