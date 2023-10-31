<?php

use Ozdemir\Aurora\Facades\Cart;

it('can not update if the quantity is negative', function() {
    // NegativeQuantityException
})->throws(Exception::class)->todo();

it('can throw validation error when the data is not valid', function() {
    // InvalidDataException
})->throws(Exception::class)->todo();
