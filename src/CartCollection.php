<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\Collection;

class CartCollection extends Collection
{
    public function updateOrAdd(CartItem $cartItem)
    {
        if ($this->has([$cartItem->hash])) {
            // if the item is already exists increase quantity..
            $this->get($cartItem->hash)->increaseQuantity($cartItem->quantity);
        } else {
            // otherwise add a new item
            $this->put($cartItem->hash, $cartItem);
        }
    }
}
