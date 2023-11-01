<?php


use Ozdemir\Aurora\CartItem;
use Ozdemir\Aurora\Facades\Cart;

it('can return cart item class when a new item added', function() {

    expect(Cart::isEmpty())->toBeTrue();

    $product = new \Ozdemir\Aurora\Tests\Models\Product();
    $product->id = 3;
    $product->price = 100;

    Cart::add(
        $item = new CartItem($product, quantity: 2),
    );

    expect(get_class($item))->toImplement(\Ozdemir\Aurora\Contracts\CartItemInterface::class)
        ->and(Cart::quantity())->toBe(2)
        ->and(Cart::isEmpty())->toBeFalse();

});
