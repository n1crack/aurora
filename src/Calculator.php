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
            ->through(collect($calculations)->reject(fn ($calculation) => $calculation === app(self::class)->skip)->toArray())
            ->thenReturn();
    }

    public static function skip($class, $callback): mixed
    {
        app(self::class)->skip = is_string($class) ? $class : get_class($class);

        $value = $callback();

        app(self::class)->skip = null;

        return $value;
    }
}
