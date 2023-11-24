<?php

use Ozdemir\Aurora\CartItem;
use Ozdemir\Aurora\Facades\Cart;
use Ozdemir\Aurora\Money;

it('can use calculators', function() {
    Cart::calculateTotalUsing([
        \Ozdemir\Aurora\Tests\Stubs\Calculators\Shipping::class,
    ]);

    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 3;
    $product->price = 30;

    Cart::add(
        new CartItem($product, quantity: 1),
    );

    expect(Cart::quantity())->toBe(1)
        ->and(Cart::count())->toBe(1)
        ->and(Cart::isEmpty())->toBeFalse()
        ->and(Cart::calculators()->toArray())->toBe([\Ozdemir\Aurora\Tests\Stubs\Calculators\Shipping::class])
        ->and(Cart::total()->amount())->toBe(40.0);
});


it('can use multiple calculators', function() {
    Cart::calculateTotalUsing([
        \Ozdemir\Aurora\Tests\Stubs\Calculators\Shipping::class,  // +10
        \Ozdemir\Aurora\Tests\Stubs\Calculators\Tax::class,    // +15
    ]);

    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 3;
    $product->price = 30;

    Cart::add(
        new CartItem($product, quantity: 1),
    );

    expect(Cart::quantity())->toBe(1)
        ->and(Cart::isEmpty())->toBeFalse()
        ->and(Cart::total()->amount())->toBe(55.0);
});

it('can have breakdowns', function() {
    Cart::calculateTotalUsing([
        \Ozdemir\Aurora\Tests\Stubs\Calculators\Shipping::class,      // +10
        \Ozdemir\Aurora\Tests\Stubs\Calculators\Tax::class,           // +15
    ]);

    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 3;
    $product->price = 30;

    Cart::add(
        new CartItem($product, quantity: 1),
        new CartItem($product, quantity: 2),
    );

    expect(Cart::total()->amount())->toBe(115.0)
        ->and(Cart::total()->breakdowns())->toHaveCount(2)
        ->and(
            json_encode(Cart::total()->breakdowns())
        )
        ->toBe(
            json_encode([
                ['label' => 'Shipping', 'value' => new Money(10)],
                ['label' => 'Tax', 'value' => new Money(15)],
            ])
        );
});


it('can have inline calculators', function() {
    Cart::calculateTotalUsing([
        function($payload, Closure $next) {
            /* @var Money $price */
            [$price, $breakdowns] = $payload;

            $shippingCost = new Money(10);

            $price = $price->add($shippingCost);

            $breakdowns[] = ['label' => 'Shipping', 'value' => $shippingCost];

            return $next([$price, $breakdowns]);
        },
        function($payload, Closure $next) {
            /* @var Money $price */
            [$price, $breakdowns] = $payload;

            $taxCost = new Money(15);

            $price = $price->add($taxCost);

            $breakdowns[] = ['label' => 'Shipping', 'value' => $taxCost];

            return $next([$price, $breakdowns]);
        },
    ]);

    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 3;
    $product->price = 30;

    Cart::add(
        new CartItem($product, quantity: 1),
        new CartItem($product, quantity: 2),
    );

    expect(Cart::total()->amount())->toBe(115.0)
        ->and(Cart::total()->breakdowns())->toHaveCount(2);
});
