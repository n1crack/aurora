<?php

namespace Ozdemir\Aurora\Traits;

trait RoundingTrait
{
    public function round($amount, $precision = null, $mode = PHP_ROUND_HALF_UP): float
    {
        $precision ??= config('cart.monetary.precision', 2);

        return round($amount, $precision, $mode);
    }
}
