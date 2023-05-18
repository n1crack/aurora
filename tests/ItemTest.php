<?php


use Ozdemir\Aurora\Facades\Cart;

it('can return cart item class when a new item added', function () {
    expect(Cart::isEmpty())->toBeTrue();

    $item = Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 2,
        'price' => 100,
    ]);

    expect($item)->toBeInstanceOf(config('cart.cart_item'));

    expect(Cart::quantity())->toBe(2);
    expect(Cart::isEmpty())->toBeFalse();
});
