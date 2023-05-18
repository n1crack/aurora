<?php

use Ozdemir\Aurora\Facades\Cart;

it('can change the item price or subtotal based on the condition', function () {
    expect(Cart::isEmpty())->toBeTrue();

    $item = Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 2,
        'price' => 100,
    ]);

    $option = new Ozdemir\Aurora\Condition([
        'name' => 'Option',
        'type' => 'other',
        'target' => 'price',
    ]);

    $option->setActions([['value' => '10%']]);

    $item->condition($option);

    expect($item->price())->toBe(110.0);
    expect($item->subtotal())->toBe(220.0);


    $item2 = Cart::add([
        'id' => 'tshirt 2',
        'name' => 'T-Shirt 2',
        'quantity' => 2,
        'price' => 100,
    ]);

    $option2 = new Ozdemir\Aurora\Condition([
        'name' => 'Option 2',
        'type' => 'other',
        'target' => 'subtotal',
    ]);

    $option2->setActions([['value' => '10%']]);
    $item2->condition($option2);

    expect($item2->price())->toBe(100.0);
    expect($item2->subtotal())->toBe(220.0);

    expect(Cart::quantity())->toBe(4);
    expect(Cart::isEmpty())->toBeFalse();
});


it('can set items condition orders', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 2,
        'price' => 100,
        'weight' => 1,
    ]);

    $tax = new Ozdemir\Aurora\Condition([
        'name' => 'Tax',
        'type' => 'tax',
        'target' => 'subtotal',
    ]);
    $tax->setActions([['value' => '6%']]);

    $other = new Ozdemir\Aurora\Condition([
        'name' => 'Other',
        'type' => 'other',
        'target' => 'subtotal',
    ]);
    $other->setActions([['value' => '125']]);

    expect(Cart::items())->toHaveCount(1);

    expect(Cart::total())->toBe(200.0);
    expect(Cart::getItemConditionsOrder())->toBe(['discount', 'other', 'shipping', 'coupon', 'tax']);

    $item = Cart::items()->first();
    $item->condition($tax);
    $item->condition($other);
    // (2*100 + 125) * 1,06
    expect(Cart::total())->toBe(344.5);

    Cart::setItemConditionsOrder(['discount', 'shipping', 'tax', 'other', 'coupon']);

    // (2*100 * 1,06) + 125
    expect(Cart::getItemConditionsOrder())->toBe(['discount', 'shipping', 'tax', 'other', 'coupon']);

    expect(Cart::total())->toBe(337.0);
});

it('can set items condition orders and update existing items', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 2,
        'price' => 100,
        'weight' => 1,
    ]);

    $tax = new Ozdemir\Aurora\Condition([
        'name' => 'Tax',
        'type' => 'tax',
        'target' => 'subtotal',
    ]);
    $tax->setActions([['value' => '6%']]);

    $other = new Ozdemir\Aurora\Condition([
        'name' => 'Other',
        'type' => 'other',
        'target' => 'subtotal',
    ]);
    $other->setActions([['value' => '125']]);

    expect(Cart::items())->toHaveCount(1);

    expect(Cart::total())->toBe(200.0);
    expect(Cart::getItemConditionsOrder())->toBe(['discount', 'other', 'shipping', 'coupon', 'tax']);

    $item = Cart::items()->first();
    $item->condition($tax);
    $item->condition($other);
    // (2*100 + 125) * 1,06
    expect(Cart::total())->toBe(344.5);

    // dont update existing items
    Cart::setItemConditionsOrder(['discount', 'coupon', 'tax', 'shipping', 'other'], false);

    expect(Cart::getItemConditionsOrder())->toBe(['discount', 'coupon', 'tax', 'shipping', 'other']);

    // (2*100 * 1,06) + 125
    expect(Cart::total())->toBe(337.0);

    Cart::add([
        'id' => 'tshirt-new',
        'name' => 'T-Shirt New',
        'quantity' => 2,
        'price' => 100,
        'weight' => 1,
    ]);

    $item = Cart::items()->last();
    $item->condition($tax);
    $item->condition($other);

    // 337 + 337
    expect(Cart::total())->toBe(674.0);
});
