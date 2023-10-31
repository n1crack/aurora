<?php

namespace Ozdemir\Aurora\Contracts;

interface Sellable
{
    public function cartItemId();

    public function cartItemPrice();

    public function cartItemQuantity();

    public function cartItemWeight();

    public function cartItemAvailability();
}
