<?php

use Ozdemir\Cart\Facades\Cart;

it('can not update if the quantity is negative', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 1,
        'price' => 30,
        'weight' => 1,
    ]);

    $item = Cart::items()->first();

    expect($item->quantity)->toBe(1);
    expect(Cart::items())->toHaveCount(1);

    Cart::update($item->hash(), 0);
})->throws(Exception::class);

it('can throw validation error when the data is not valid', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => -5,
        'price' => 30,
        'weight' => 1,
    ]);
})->throws(Exception::class);
