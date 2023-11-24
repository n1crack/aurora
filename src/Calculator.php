<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\Collection;

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
