<?php

namespace Ozdemir\Aurora;

class CalculationResult
{
    protected float $total;
    protected mixed $breakdowns;

    public function __construct($total, $breakdowns)
    {
        $this->total = $total;
        $this->breakdowns = $breakdowns;
    }

    public function total(): float
    {
        return $this->total;
    }

    public function breakdowns(): mixed
    {
        return $this->breakdowns;
    }
}
