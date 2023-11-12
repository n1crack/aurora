<?php

use Ozdemir\Aurora\CartItem;
use Ozdemir\Aurora\Facades\Cart;
use Ozdemir\Aurora\Tests\Stubs\Models\Product;

it('can have meta data', function() {
    $product = new Product();
    $product->id = 2;
    $product->price = 30;
    $product->weight = 4;

    Cart::add(new CartItem($product, 2));


    expect(Cart::total()->amount())->toBe(60.0);


    Cart::setMeta('coupon', 'ABC123');

    expect(Cart::meta()->count())->toBe(1)
        ->and(Cart::meta()->get('coupon'))->toBe('ABC123');


    Cart::removeMeta('coupon');

    expect(Cart::meta()->count())->toBe(0);


});

it('can have items with meta data', function() {
    $product = new Product();
    $product->id = 2;
    $product->price = 30;
    $product->weight = 4;

    Cart::add(
        $cartItem = (new CartItem($product, 2))->withMeta('testMeta', 'ABC123')
    );

    expect(Cart::total()->amount())->toBe(60.0)
        ->and($cartItem->meta->get('testMeta'))->toBe('ABC123');
});
