<?php

use Ozdemir\Aurora\CartItem;
use Ozdemir\Aurora\Facades\Cart;

it('can snapshot the current cart', function() {

    $product = new \Ozdemir\Aurora\Tests\Models\Product();
    $product->id = 3;
    $product->price = 30;
    $product->weight = 1;

    Cart::add(
        (new CartItem($product, 1))
            ->withOption('color', 'blue')
            ->withOption('size', 's')
    );
    expect(Cart::snapshot())->toBeString();
});

it('can unserialize from serialized string', function() {
    expect(Cart::isEmpty())->toBeTrue();
    $product = new \Ozdemir\Aurora\Tests\Models\Product();
    $product->id = 3;
    $product->price = 40;
    $product->weight = 1;

    Cart::add(
        (new CartItem($product, 3)) // 40 * 3 = 120
            ->withOption('color', 'blue')
            ->withOption('size', 's', '10') // + 10
            ->withOption('size', 's', '5', true) // + 40 * 0,05 =  + 2 // total 40 + 10 + 2 (56 * 3 = 156)
    );

    // Get serialized text..
    $snapshot = Cart::snapshot();

    // Clear
    Cart::clear();

    // It is now empty
    expect(Cart::isEmpty())->toBeTrue();

    // restore the cart from snapshot
    Cart::restore($snapshot);

    expect(Cart::subtotal()->amount())->toBe(156.0)
        ->and(Cart::total()->amount())->toBe(156.0)
        ->and(Cart::items()->count())->toBe(1)
        ->and(Cart::quantity())->toBe(3);
});
