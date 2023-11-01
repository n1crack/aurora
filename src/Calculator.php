<?php

namespace Ozdemir\Aurora;

class Calculator
{
    public static function calculate($price, $pipeline = [])
    {
        return app('pipeline')
            ->send([$price, []])
            ->through($pipeline)
            ->thenReturn();
    }
}
