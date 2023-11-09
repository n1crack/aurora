<?php

namespace Ozdemir\Aurora;

class Calculator
{
    public ?string $skip = null;

    public CartCalculatorCollection $pipeline;

    public function __construct()
    {
        $this->pipeline = new CartCalculatorCollection(config('cart.calculate_using'));
    }

    public function calculate($price, $calculations = [])
    {
        return resolve('pipeline')
            ->send([$price, []])
            ->through(collect($calculations)->reject(fn ($calculation) => $calculation === $this->skip)->toArray())
            ->thenReturn();
    }

    public function skip($class, $callback): mixed
    {
        $this->skip = is_string($class) ? $class : get_class($class);

        $value = $callback();

        $this->skip = null;

        return $value;
    }
}
