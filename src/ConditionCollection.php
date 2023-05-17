<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\Collection;

class ConditionCollection extends Collection
{
    public function sortByOrder($order)
    {
        return $this->sortBy(fn ($item) => array_search($item->type, $order, true))->values();
    }

    public function calculateSubTotal($subtotal)
    {
        return $this->map(function($condition) use (&$subtotal) {
            $condition->value = $condition->calculate($subtotal);
            $subtotal += $condition->value;

            return $condition;
        });
    }

    public function filterType($type)
    {
        return $this->when($type, fn ($conditions) => $conditions->where('type', $type));
    }
}
