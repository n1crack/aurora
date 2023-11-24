<?php

namespace Ozdemir\Aurora;

use Ozdemir\Aurora\Contracts\MoneyInterface;

class Money implements MoneyInterface
{
    public function __construct(readonly private float|int $amount, private $breakdowns = [])
    {
    }

    public function amount(): float|int
    {
        return $this->amount;
    }

    public function breakdowns(): array
    {
        return $this->breakdowns;
    }

    public function setBreakdowns($breakdowns): static
    {
        $this->breakdowns = $breakdowns;

        return new self($this->amount, $breakdowns);
    }

    public function round($precision = null, $mode = PHP_ROUND_HALF_UP): static
    {
        $precision ??= config('cart.monetary.precision', 2);

        $amount = round($this->amount, $precision, $mode);

        return $this->newInstance($amount, $this->breakdowns);
    }

    public function add(MoneyInterface $addend): static
    {
        return $this->newInstance($this->amount + $addend->amount);
    }

    public function subtract(MoneyInterface $subtrahend): static
    {
        return $this->newInstance($this->amount - $subtrahend->amount);
    }

    public function multiply($multiplier): static
    {
        return $this->newInstance($this->amount * $multiplier);
    }

    public function newInstance($amount, $breakdowns = []): static
    {
        return new self($amount, $breakdowns);
    }
}
