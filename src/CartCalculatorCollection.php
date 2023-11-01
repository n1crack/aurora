<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\Collection;

class CartCalculatorCollection extends Collection
{
    public $skipped = null;

    public function reload($items = []): static
    {
        $this->items = $items;

        return $this;
    }
}
