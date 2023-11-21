<?php

namespace Ozdemir\Aurora\Contracts;

interface MoneyInterface
{
    public function breakdowns(): array;

    public function setBreakdowns($breakdowns): static;

    public function amount(): float|int;

    public function round($precision = null, $mode = PHP_ROUND_HALF_UP): static;

    public function add(MoneyInterface $addend): static;

    public function subtract(MoneyInterface $subtrahend): static;

    public function multiply($multiplier): static;

    public function divide($divisor): static;

    public function newInstance($amount, $breakdowns = []): static;

    public function __toString();
}
