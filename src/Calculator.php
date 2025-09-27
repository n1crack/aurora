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
        $cartStateHash = $this->getCartStateHash();

        return once(fn() => $this->processResults($cartStateHash, $price, $calculations));
    }

    /**
     * Hash value here is only for memoization
     */
    public function processResults($hash, $price, $calculations = [])
    {
        return Pipeline::send([$price, []])
           ->through(
               collect($calculations)
                   ->reject(fn($calculation) => $calculation === $this->skip)
                   ->toArray()
           )
           ->thenReturn();
    }

    public function getCartStateHash()
    {
        return sha1(
            \Ozdemir\Aurora\Facades\Cart::items()->pluck('quantity', 'hash')->toJson()
        );
    }

    public function skip($class, $callback): mixed
    {
        $this->skip = is_string($class) ? $class : get_class($class);

        $value = $callback();

        $this->skip = null;

        return $value;
    }
}
