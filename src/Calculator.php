<?php

namespace Ozdemir\Aurora;

class Calculator
{
    public $skipCalculation = null;

    public static function calculate($price, $pipeline = [])
    {
        $skip = app(CartCalculatorCollection::class)->skipped;

        return app('pipeline')
            ->send([$price, []])
            ->through(collect($pipeline)->reject(fn ($value) => $value === $skip)->toArray())
            ->thenReturn();
    }

    public static function skip($class, $callback): mixed
    {
        $calcCollection = app(CartCalculatorCollection::class);

        $calcCollection->skipped = is_string($class) ? $class : get_class($class);

        $value = $callback();

        $calcCollection->skipped = null;

        return $value;
    }


}
