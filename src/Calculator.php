<?php

namespace Ozdemir\Aurora;

class Calculator
{
    public ?string $skip = null;

    public CartCalculatorCollection $pipeline;

    public function __construct()
    {
        $this->pipeline = new CartCalculatorCollection();
    }

    public static function calculate($price, $calculations = [])
    {
        return app('pipeline')
            ->send([$price, []])
            ->through(collect($calculations)->reject(fn ($calculation) => $calculation === app(Calculator::class)->skip)->toArray())
            ->thenReturn();
    }

    public static function skip($class, $callback): mixed
    {
        app(Calculator::class)->skip = is_string($class) ? $class : get_class($class);

        $value = $callback();

        app(Calculator::class)->skip = null;

        return $value;
    }
}
