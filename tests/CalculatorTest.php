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
        ->and(Cart::calculators()->get(\Ozdemir\Aurora\Enums\CartCalculator::TOTAL->value))->toBe([\Ozdemir\Aurora\Tests\Stubs\Calculators\Shipping::class])
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


it('can have calculators on cart subtotal', function() {

    Cart::calculateSubtotalUsing([
        \Ozdemir\Aurora\Tests\Stubs\Calculators\Discount::class,  // - 5%
    ]);

    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 3;
    $product->price = 200;

    Cart::add(
        new CartItem($product, quantity: 1),
    );

    expect(Cart::quantity())->toBe(1)
        ->and(Cart::calculators()->get(\Ozdemir\Aurora\Enums\CartCalculator::SUBTOTAL->value))->toBe([\Ozdemir\Aurora\Tests\Stubs\Calculators\Discount::class,])
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


it('can have calculators on sellable item subtotal', function() {

    Cart::calculateItemSubtotalUsing([
        \Ozdemir\Aurora\Tests\Stubs\Calculators\Discount::class => [3, 6],  // for the items that has these ids run the discount -5%
    ]);

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
        ->and(Cart::subtotal()->amount())->toBe(435.0)
        ->and(Cart::total()->amount())->toBe(435.0);
});

it('can have default calculators on subtotals of every sellable items', function() {
    Cart::calculateItemSubtotalUsing([
        \Ozdemir\Aurora\Tests\Stubs\Calculators\Discount::class,  // for the items that has these ids run the discount -5%
    ]);

    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 3;
    $product->price = 200;

    $product2 = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product2->id = 4;
    $product2->price = 50;

    Cart::add(
        new CartItem($product, quantity: 1), // 200 * 0,95 = 190
        new CartItem($product2, quantity: 3) // 150 * 0,95  = 142,5
    );

    expect(Cart::quantity())->toBe(4)
        ->and(Cart::subtotal()->amount())->toBe(332.5)
        ->and(Cart::total()->amount())->toBe(332.5);
});

it('can have inline calculators on sellable item subtotal', function() {
    Cart::calculateItemSubtotalUsing([
        \Ozdemir\Aurora\Tests\Stubs\Calculators\Discount::class,  // for the items that has these ids run the discount -5%
        function($payload, Closure $next) { // simply add + 0.01$ for every items subtotal value.
            [$price, $breakdowns] = $payload;
            $price = $price->add(new Money(0.01));
            $breakdowns[] = ['label' => 'Custom Shipping', 'value' => '0.01$'];

            return $next([$price, $breakdowns]);
        },
    ]);

    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 3;
    $product->price = 200;

    $product2 = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product2->id = 4;
    $product2->price = 50;

    Cart::add(
        new CartItem($product, quantity: 1), // 200 * 0,95 = 190 + 0.01
        new CartItem($product2, quantity: 3) // 150 * 0,95  = 142,5 + 0.01
    );

    expect(Cart::quantity())->toBe(4)
        ->and(Cart::subtotal()->amount())->toBe(332.52)
        ->and(Cart::items()->first()->subtotal()->amount())->toBe(190.01)
        ->and(Cart::items()->first()->subtotal()->breakdowns()[1])->toBe(['label' => 'Custom Shipping', 'value' => '0.01$']);
});
