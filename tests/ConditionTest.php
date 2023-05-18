<?php

use Ozdemir\Aurora\Facades\Cart;

it('can add a new condition', function() {
    $condition = new Ozdemir\Aurora\Condition([
        'name' => 'VAT (6%)',
        'type' => 'tax',
        'target' => 'subtotal',
    ]);

    $condition->setActions([
        [
            'value' => '6%',
            'rules' => [],
        ],
    ]);

    expect(Cart::hasCondition('VAT (6%)'))->toBeFalse();

    Cart::condition($condition);

    expect(Cart::hasCondition('VAT (6%)'))->toBeTrue();

    expect(Cart::conditions())->toBeInstanceOf(\Ozdemir\Aurora\ConditionCollection::class);
    expect(Cart::conditions())->toHaveCount(1);

    expect(Cart::conditions()->first())->toBeInstanceOf(\Ozdemir\Aurora\Condition::class);
    expect(Cart::conditions()->first()->name)->toBe('VAT (6%)');
});

it('can remove a condition', function() {
    $condition = new Ozdemir\Aurora\Condition([
        'name' => 'VAT (6%)',
        'type' => 'tax',
        'target' => 'subtotal',
    ]);

    $condition->setActions([
        [
            'value' => '6%',
            'rules' => [],
        ],
    ]);

    Cart::condition($condition);

    expect(Cart::hasCondition('VAT (6%)'))->toBeTrue();

    Cart::removeCondition('VAT (6%)');

    expect(Cart::hasCondition('VAT (6%)'))->toBeFalse();

    expect(Cart::conditions())->toBeInstanceOf(\Ozdemir\Aurora\ConditionCollection::class);
    expect(Cart::conditions())->toHaveCount(0);
});

it('can add value to total', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 2,
        'price' => 30,
        'weight' => 1,
    ]);

    Cart::add([
        'id' => 'tv',
        'name' => 'Television',
        'quantity' => 1,
        'price' => 470,
        'weight' => 8,
    ]);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::subtotal())->toBe(530.0);
    expect(Cart::total())->toBe(530.0);

    $condition = new Ozdemir\Aurora\Condition([
        'name' => 'Shipping',
        'type' => 'shipping',
        'target' => 'total',
    ]);

    $condition->setActions([
        [
            'value' => '25',
        ],
    ]);

    Cart::condition($condition);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::subtotal())->toBe(530.0);
    expect(Cart::total())->toBe(555.0);
});

it('can sub value from total ', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 2,
        'price' => 30,
        'weight' => 1,
    ]);

    Cart::add([
        'id' => 'tv',
        'name' => 'Television',
        'quantity' => 1,
        'price' => 470,
        'weight' => 8,
    ]);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::subtotal())->toBe(530.0);
    expect(Cart::total())->toBe(530.0);

    $condition = new Ozdemir\Aurora\Condition([
        'name' => 'Discount',
        'type' => 'discount',
        'target' => 'total',
    ]);

    $condition->setActions([
        [
            'value' => '-25',
        ],
    ]);

    Cart::condition($condition);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::subtotal())->toBe(530.0);
    expect(Cart::total())->toBe(505.0);
});

it('can add percent value to total ', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 2,
        'price' => 30,
        'weight' => 1,
    ]);

    Cart::add([
        'id' => 'tv',
        'name' => 'Television',
        'quantity' => 1,
        'price' => 470,
        'weight' => 8,
    ]);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::subtotal())->toBe(530.0);
    expect(Cart::total())->toBe(530.0);

    $condition = new Ozdemir\Aurora\Condition([
        'name' => 'Tax (6%)',
        'type' => 'tax',
        'target' => 'total',
    ]);

    $condition->setActions([
        [
            'value' => '6%',
        ],
    ]);

    Cart::condition($condition);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::subtotal())->toBe(530.0);
    expect(Cart::total())->toBe(561.8);
});

it('can sub percent value from total ', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 2,
        'price' => 30,
        'weight' => 1,
    ]);

    Cart::add([
        'id' => 'tv',
        'name' => 'Television',
        'quantity' => 1,
        'price' => 470,
        'weight' => 8,
    ]);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::subtotal())->toBe(530.0);
    expect(Cart::total())->toBe(530.0);

    $condition = new Ozdemir\Aurora\Condition([
        'name' => 'Tax (6%)',
        'type' => 'tax',
        'target' => 'total',
    ]);

    $condition->setActions([
        [
            'value' => '-6%',
        ],
    ]);

    Cart::condition($condition);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::subtotal())->toBe(530.0);
    expect(Cart::total())->toBe(498.2);
});

it('can set condition orders', function() {
    $shipping = new Ozdemir\Aurora\Condition([
        'name' => 'Shipping',
        'type' => 'shipping',
        'target' => 'subtotal',
    ]);
    $shipping->setActions([['value' => '25']]);
    Cart::condition($shipping);

    $tax = new Ozdemir\Aurora\Condition([
        'name' => 'Tax',
        'type' => 'tax',
        'target' => 'subtotal',
    ]);
    $tax->setActions([['value' => '6%']]);
    Cart::condition($tax);

    $other = new Ozdemir\Aurora\Condition([
        'name' => 'Other',
        'type' => 'other',
        'target' => 'subtotal',
    ]);
    $other->setActions([['value' => '125']]);
    Cart::condition($other);

    $coupon = new Ozdemir\Aurora\Condition([
        'name' => 'Coupon',
        'type' => 'coupon',
        'target' => 'total',
    ]);
    $coupon->setActions([['value' => '-5']]);
    Cart::condition($coupon);

    $discount = new Ozdemir\Aurora\Condition([
        'name' => 'Discount',
        'type' => 'discount',
        'target' => 'subtotal',
    ]);
    $discount->setActions([['value' => '-10%']]);
    Cart::condition($discount);

    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 2,
        'price' => 100,
        'weight' => 1,
    ]);

    expect(Cart::getConditionsOrder())->toBe(['discount', 'other', 'shipping', 'coupon', 'tax']);
    expect(Cart::conditions()->first()->name)->toBe('Discount');
    expect(Cart::conditions()->get(1)->name)->toBe('Other');
    expect(Cart::conditions()->get(2)->name)->toBe('Shipping');
    expect(Cart::conditions()->get(3)->name)->toBe('Coupon');
    expect(Cart::conditions()->last()->name)->toBe('Tax');

    // (((200*0,9)+125)+25)*1,06  = 349.8
    expect(Cart::subtotal())->toBe(349.8);
    // 349.8 - 5.0 = 344.8
    expect(Cart::total())->toBe(344.8);

    Cart::setConditionsOrder(['other', 'shipping', 'tax', 'coupon', 'discount']);

    expect(Cart::getConditionsOrder())->toBe(['other', 'shipping', 'tax', 'coupon', 'discount']);
    expect(Cart::conditions()->first()->name)->toBe('Other');
    expect(Cart::conditions()->get(1)->name)->toBe('Shipping');
    expect(Cart::conditions()->get(2)->name)->toBe('Tax');
    expect(Cart::conditions()->get(3)->name)->toBe('Coupon');
    expect(Cart::conditions()->last()->name)->toBe('Discount');

    // (200+125+25)*1,06*0,9 = 333.9
    expect(Cart::subtotal())->toBe(333.9);
    // 333.9 - 5.0 = 328.9
    expect(Cart::total())->toBe(328.9);
});

