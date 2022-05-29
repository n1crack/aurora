<?php

use Ozdemir\Cart\Facades\Cart;

it('can add a new condition', function() {
    $condition = new Ozdemir\Cart\Condition([
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

    expect(Cart::conditions())->toBeInstanceOf(\Ozdemir\Cart\ConditionCollection::class);
    expect(Cart::conditions())->toHaveCount(1);

    expect(Cart::conditions()->first())->toBeInstanceOf(\Ozdemir\Cart\Condition::class);
    expect(Cart::conditions()->first()->name)->toBe('VAT (6%)');
});

it('can remove a condition', function() {
    $condition = new Ozdemir\Cart\Condition([
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

    expect(Cart::conditions())->toBeInstanceOf(\Ozdemir\Cart\ConditionCollection::class);
    expect(Cart::conditions())->toHaveCount(0);
});

it('can add value to total ', function() {
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
    expect(Cart::subtotal())->toBe(530);
    expect(Cart::total())->toBe(530);

    $condition = new Ozdemir\Cart\Condition([
        'name' => 'Shipping',
        'type' => 'shipping',
        'target' => 'subtotal',
    ]);

    $condition->setActions([
        [
            'value' => '25',
        ],
    ]);

    Cart::condition($condition);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::subtotal())->toBe(530);
    expect(Cart::total())->toBe(555);
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
    expect(Cart::subtotal())->toBe(530);
    expect(Cart::total())->toBe(530);

    $condition = new Ozdemir\Cart\Condition([
        'name' => 'Discount',
        'type' => 'discount',
        'target' => 'subtotal',
    ]);

    $condition->setActions([
        [
            'value' => '-25',
        ],
    ]);

    Cart::condition($condition);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::subtotal())->toBe(530);
    expect(Cart::total())->toBe(505);
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
    expect(Cart::subtotal())->toBe(530);
    expect(Cart::total())->toBe(530);

    $condition = new Ozdemir\Cart\Condition([
        'name' => 'Tax (6%)',
        'type' => 'tax',
        'target' => 'subtotal',
    ]);

    $condition->setActions([
        [
            'value' => '6%',
        ],
    ]);

    Cart::condition($condition);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::subtotal())->toBe(530);
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
    expect(Cart::subtotal())->toBe(530);
    expect(Cart::total())->toBe(530);

    $condition = new Ozdemir\Cart\Condition([
        'name' => 'Tax (6%)',
        'type' => 'tax',
        'target' => 'subtotal',
    ]);

    $condition->setActions([
        [
            'value' => '-6%',
        ],
    ]);

    Cart::condition($condition);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::subtotal())->toBe(530);
    expect(Cart::total())->toBe(498.2);
});

it('can set condition orders', function() {
    $shipping = new Ozdemir\Cart\Condition([
        'name' => 'Shipping',
        'type' => 'shipping',
        'target' => 'subtotal',
    ]);
    $shipping->setActions([['value' => '25']]);
    Cart::condition($shipping);

    $tax = new Ozdemir\Cart\Condition([
        'name' => 'Tax',
        'type' => 'tax',
        'target' => 'subtotal',
    ]);
    $tax->setActions([['value' => '6%']]);
    Cart::condition($tax);

    $other = new Ozdemir\Cart\Condition([
        'name' => 'Other',
        'type' => 'other',
        'target' => 'subtotal',
    ]);
    $other->setActions([['value' => '125']]);
    Cart::condition($other);

    $discount = new Ozdemir\Cart\Condition([
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

    expect(Cart::getConditionsOrder())->toBe(['discount', 'other', 'tax', 'shipping']);
    expect(Cart::conditions()->first()->name)->toBe('Discount');
    expect(Cart::conditions()->get(1)->name)->toBe('Other');
    expect(Cart::conditions()->get(2)->name)->toBe('Tax');
    expect(Cart::conditions()->last()->name)->toBe('Shipping');
    // (((200*0,9)+125)*1,06)+25
    expect(Cart::total())->toBe(348.3);

    Cart::setConditionsOrder(['other', 'shipping', 'tax', 'discount']);

    expect(Cart::getConditionsOrder())->toBe(['other', 'shipping', 'tax', 'discount']);
    expect(Cart::conditions()->first()->name)->toBe('Other');
    expect(Cart::conditions()->get(1)->name)->toBe('Shipping');
    expect(Cart::conditions()->get(2)->name)->toBe('Tax');
    expect(Cart::conditions()->last()->name)->toBe('Discount');
    // (200+125+25)*1,06*0,9
    expect(Cart::total())->toBe(333.9);
});


it('can set items condition orders', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 2,
        'price' => 100,
        'weight' => 1,
    ]);

    $tax = new Ozdemir\Cart\Condition([
        'name' => 'Tax',
        'type' => 'tax',
        'target' => 'subtotal',
    ]);
    $tax->setActions([['value' => '6%']]);

    $other = new Ozdemir\Cart\Condition([
        'name' => 'Other',
        'type' => 'other',
        'target' => 'subtotal',
    ]);
    $other->setActions([['value' => '125']]);

    expect(Cart::items())->toHaveCount(1);

    expect(Cart::total())->toBe(200);
    expect(Cart::getItemConditionsOrder())->toBe(['discount', 'other', 'tax', 'shipping']);

    $item = Cart::items()->first();
    $item->condition($tax);
    $item->condition($other);
    // (2*100 + 125) * 1,06
    expect(Cart::total())->toBe(344.5);

    Cart::setItemConditionsOrder(['discount', 'shipping', 'tax', 'other']);

    // (2*100 * 1,06) + 125
    expect(Cart::getItemConditionsOrder())->toBe(['discount', 'shipping', 'tax', 'other']);

    expect(Cart::total())->toBe(337.0);
});

it('can set items condition orders without update existing items', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 2,
        'price' => 100,
        'weight' => 1,
    ]);

    $tax = new Ozdemir\Cart\Condition([
        'name' => 'Tax',
        'type' => 'tax',
        'target' => 'subtotal',
    ]);
    $tax->setActions([['value' => '6%']]);

    $other = new Ozdemir\Cart\Condition([
        'name' => 'Other',
        'type' => 'other',
        'target' => 'subtotal',
    ]);
    $other->setActions([['value' => '125']]);

    expect(Cart::items())->toHaveCount(1);

    expect(Cart::total())->toBe(200);
    expect(Cart::getItemConditionsOrder())->toBe(['discount', 'other', 'tax', 'shipping']);

    $item = Cart::items()->first();
    $item->condition($tax);
    $item->condition($other);
    // (2*100 + 125) * 1,06
    expect(Cart::total())->toBe(344.5);

    // dont update existing items
    Cart::setItemConditionsOrder(['discount', 'shipping', 'tax', 'other'], false);

    // (2*100 * 1,06) + 125
    expect(Cart::getItemConditionsOrder())->toBe(['discount', 'shipping', 'tax', 'other']);

    expect(Cart::total())->toBe(344.5);

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

    // 344.5 + 337
    expect(Cart::total())->toBe(681.5);
});
