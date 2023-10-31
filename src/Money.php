<?php

namespace Ozdemir\Aurora;

class Money
{
    public function __construct(public float|int $amount)
    {
    }

    public function amount(): float|int
    {
        return $this->amount;
    }

    public function round($precision = null, $mode = PHP_ROUND_HALF_UP): static
    {
        if (is_null($precision)) {
            $precision = 2;
//            $precision = config('cart.precision');
        }

        $amount = round($this->amount, $precision, $mode);

        return $this->newInstance($amount);
    }

    public function add(self $addend): static
    {
        return $this->newInstance($this->amount + $addend->amount);
    }

    public function subtract(self $subtrahend): static
    {
        return $this->newInstance($this->amount - $subtrahend->amount);
    }

    public function multiply($multiplier): static
    {
        return $this->newInstance($this->amount * $multiplier);
    }

    public function divide($divisor): static
    {
        return $this->newInstance($this->amount / $divisor);
    }

    private function newInstance($amount): static
    {
        return new self($amount);
    }

    public function isZero(): bool
    {
        return $this->amount == 0;
    }

    public function __toString()
    {
        return (string) $this->amount;
    }
}
