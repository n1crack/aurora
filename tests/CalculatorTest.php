<?php

use Ozdemir\Aurora\CartItem;
use Ozdemir\Aurora\Facades\Cart;
use Ozdemir\Aurora\Money;

it('can use calculators', function() {
    Cart::calculateUsing(\Ozdemir\Aurora\Enums\CartCalculator::TOTAL, [
        \Ozdemir\Aurora\Tests\Stubs\Calculators\Shipping::class,
    ]);

    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 3;
    $product->price = 30;

    Cart::add(
        new CartItem($product, quantity: 1),
    );

    expect(Cart::quantity())->toBe(1)
        ->and(Cart::isEmpty())->toBeFalse()
        ->and(Cart::calculators()->toArray())->toBe(
            [
                \Ozdemir\Aurora\Enums\CartCalculator::TOTAL->value => [
                    \Ozdemir\Aurora\Tests\Stubs\Calculators\Shipping::class,
                ],
            ]
        )
        ->and(Cart::total()->amount())->toBe(40.0);
});


it('can use multiple calculators', function() {
    Cart::calculateUsing(\Ozdemir\Aurora\Enums\CartCalculator::TOTAL, [
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
    Cart::calculateUsing(\Ozdemir\Aurora\Enums\CartCalculator::TOTAL, [
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
    Cart::calculateUsing(\Ozdemir\Aurora\Enums\CartCalculator::TOTAL, [
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


it('can have calculators on cart subtotal', function() {

    Cart::calculateUsing(\Ozdemir\Aurora\Enums\CartCalculator::SUBTOTAL, [
        \Ozdemir\Aurora\Tests\Stubs\Calculators\Discount::class,  // - 5%
    ]);

    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 3;
    $product->price = 200;

    Cart::add(
        new CartItem($product, quantity: 1),
    );

    expect(Cart::quantity())->toBe(1)
        ->and(Cart::isEmpty())->toBeFalse()
        ->and(Cart::calculators()->toArray())->toBe(
            [
                \Ozdemir\Aurora\Enums\CartCalculator::SUBTOTAL->value => [
                    \Ozdemir\Aurora\Tests\Stubs\Calculators\Discount::class,
                ],
            ]
        )
        ->and(Cart::subtotal()->amount())->toBe(190.0)
        ->and(Cart::total()->amount())->toBe(190.0)
        ->and(
            json_encode(Cart::subtotal()->breakdowns())
        )
        ->toBe(
            json_encode([
                ['label' => 'Discount', 'value' => new Money(-10)],
            ])
        );
});


it('can have calculators on buyable item subtotal', function() {

    Cart::calculateItemsUsing(
        \Ozdemir\Aurora\Enums\CartItemCalculator::SUBTOTAL,
        [
            \Ozdemir\Aurora\Tests\Stubs\Calculators\Discount::class => [3, 6],  // for the items that has these ids run the discount -5%
        ]
    );

    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 3;
    $product->price = 200;

    $product2 = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product2->id = 4;
    $product2->price = 50;

    $product3 = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product3->id = 6;
    $product3->price = 100;

    Cart::add(
        new CartItem($product, quantity: 1), // 200 * 0,95 = 190
        new CartItem($product2, quantity: 3), // 150  = 150
        new CartItem($product3, quantity: 1),  // 100 * 0,95 = 95
    );

    expect(Cart::quantity())->toBe(5)
        ->and(Cart::count())->toBe(3)
        ->and(Cart::isEmpty())->toBeFalse()
        ->and(Cart::subtotal()->amount())->toBe(435.0)
        ->and(Cart::total()->amount())->toBe(435.0);
});
