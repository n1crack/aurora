<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Pipeline;

class Calculator
{
    public ?string $skip = null;

    private Collection $calculators;

    public function __construct()
    {
        $this->calculators = new Collection(config('cart.calculate_using'));
    }

    public function calculators(): Collection
    {
        return $this->calculators;
    }

    public function calculate($price, $calculations = [])
    {
        return once(fn() => $this->processResults($price, $calculations));
    }

    public function processResults($price, $calculations = [])
    {
         return Pipeline::send([$price, []])
            ->through(
                collect($calculations)
                    ->reject(fn($calculation) => $calculation === $this->skip)
                    ->toArray()
            )
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
