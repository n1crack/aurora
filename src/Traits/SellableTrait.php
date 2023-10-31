<?php

namespace Ozdemir\Aurora\Traits;

trait SellableTrait
{

    public function cartItemId()
    {
        return $this->id;
    }

    public function cartItemPrice()
    {
        return $this->price;
    }

    public function cartItemBasePrice()
    {
        return $this->basePrice;
    }

    public function cartItemQuantity()
    {
        return $this->quantity;
    }

    public function cartItemWeight()
    {
        return $this->weight;
    }

    public function cartItemAvailability(): bool
    {
        return true;
    }
}
