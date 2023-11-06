<?php


use Ozdemir\Aurora\CartItem;
use Ozdemir\Aurora\Facades\Cart;
use Ozdemir\Aurora\Money;

it('has checknum and can be validated', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 2;
    $product->price = 30;
    $product->weight = 4;

    Cart::add(new CartItem($product, 2));

    expect(Cart::quantity())->toBe(2)
        ->and(Cart::checksum())->toBe('5b520de298c71f3deaa3544809e0be2e')
        ->and(Cart::validate('5b520de298c71f3deaa3544809e0be2e'))->toBeTrue();

    Cart::add(new CartItem($product, 1));

    expect(Cart::quantity())->toBe(3)
        ->and(Cart::validate('5b520de298c71f3deaa3544809e0be2e'))->toBeFalse();

});


it('has a checknum hash and can be validated', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 2;
    $product->price = 30;
    $product->weight = 4;

    Cart::add(new CartItem($product, 2));

    expect(Cart::quantity())->toBe(2)
        ->and(Cart::checksum())->toBe('5b520de298c71f3deaa3544809e0be2e')
        ->and(Cart::validate('5b520de298c71f3deaa3544809e0be2e'))->toBeTrue();

    Cart::add(new CartItem($product, 1));

    expect(Cart::quantity())->toBe(3)
        ->and(Cart::validate('5b520de298c71f3deaa3544809e0be2e'))->toBeFalse();


    Cart::clear();

    expect(Cart::quantity())->toBe(0)
        ->and(Cart::validate('5b520de298c71f3deaa3544809e0be2e'))->toBeFalse();

});


it('has a checknum that can be changed if the calculators changes', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 2;
    $product->price = 30;
    $product->weight = 4;

    Cart::add(new CartItem($product, 2));

    expect(Cart::quantity())->toBe(2)
        ->and(Cart::total()->amount())->toBe(60.0)
        ->and(Cart::checksum())->toBe('5b520de298c71f3deaa3544809e0be2e')
        ->and(Cart::validate('5b520de298c71f3deaa3544809e0be2e'))->toBeTrue();

    Cart::calculateTotalUsing([
        \Ozdemir\Aurora\Tests\Stubs\Calculators\Shipping::class,  // +10
        \Ozdemir\Aurora\Tests\Stubs\Calculators\Tax::class,    // +15
    ]);

    expect(Cart::quantity())->toBe(2)
        ->and(Cart::total()->amount())->toBe(85.0)
        ->and(Cart::validate('5b520de298c71f3deaa3544809e0be2e'))->toBeFalse();

});


it('has a checknum that can be changed if the inline calculators changes', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 2;
    $product->price = 30;
    $product->weight = 4;

    Cart::add(new CartItem($product, 2));

    expect(Cart::quantity())->toBe(2)
        ->and(Cart::total()->amount())->toBe(60.0)
        ->and(Cart::checksum())->toBe('5b520de298c71f3deaa3544809e0be2e')
        ->and(Cart::validate('5b520de298c71f3deaa3544809e0be2e'))->toBeTrue();

    Cart::calculateTotalUsing([
        function($payload, Closure $next) { // simply add + 0.01$ for every items subtotal value.
            [$price, $breakdowns] = $payload;
            $price = $price->add(new Money(10));
            $breakdowns[] = ['label' => 'Custom Shipping', 'value' => '10$'];

            return $next([$price, $breakdowns]);
        },
    ]);

    expect(Cart::quantity())->toBe(2)
        ->and(Cart::total()->amount())->toBe(70.0)
        ->and(Cart::checksum())->toBe('19acbc85fc3a5e4d74e08f3c78275736')
        ->and(Cart::validate('5b520de298c71f3deaa3544809e0be2e'))->toBeFalse();

    Cart::calculateTotalUsing([
        function($payload, Closure $next) { // simply add + 0.01$ for every items subtotal value.
            [$price, $breakdowns] = $payload;
            $price = $price->add(new Money(11));
            $breakdowns[] = ['label' => 'Custom Shipping', 'value' => '11$'];

            return $next([$price, $breakdowns]);
        },
    ]);

    expect(Cart::quantity())->toBe(2)
        ->and(Cart::total()->amount())->toBe(71.0)
        ->and(Cart::checksum())->toBe('0ff00255271d88df533d68a60a498dff')
        ->and(Cart::validate('19acbc85fc3a5e4d74e08f3c78275736'))->toBeFalse();
});
