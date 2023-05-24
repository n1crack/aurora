<?php

use Laravel\SerializableClosure\SerializableClosure;
use Ozdemir\Aurora\Condition;
use Ozdemir\Aurora\Facades\Cart;

it('can serialize the current', function() {

    $condition = new Condition([
        'name' => 'Condition',
        'type' => 'tax',
        'target' => 'subtotal',
    ]);
    $condition->setActions([
        [
            'value' => '125',
            'rules' => new SerializableClosure(fn () => Cart::subtotal() > 50),
        ],
    ]);
    Cart::condition($condition);

    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 3,
        'price' => 30,
        'weight' => 1,
        'attributes' => [
            'color' => ['label' => 'Blue', 'value' => 'blue'],
            'size' => ['label' => 'Small', 'value' => 's'],
        ],
    ]);
    expect(Cart::total())->toBe(215.0);
    expect(Cart::items())->toHaveCount(1);
    expect(Cart::serialize())->toBeString();
});

it('can unserialize from serialized string', function() {
    expect(Cart::isEmpty())->toBeTrue();
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 3,
        'price' => 30,
        'weight' => 1,
        'attributes' => [
            'color' => ['label' => 'Blue', 'value' => 'blue'],
            'size' => ['label' => 'Small', 'value' => 's'],
        ],
    ]);

    $condition = new Condition([
        'name' => 'Condition A',
        'type' => 'coupon',
        'target' => 'subtotal',
    ]);

    $condition->setActions([
        [
            'value' => '-50%',
            'rules' => new SerializableClosure(function() {
                // we shouldn't check Cart::subtotal() here
                // since we apply the discount on subtotal.
                // Instead, we check product subtotal.
                return Cart::getItemSubTotal() > 100;
            }),
        ],
    ]);

    Cart::condition($condition);

    // Get serialized text..
    $serialized = Cart::serialize();

    // Clear
    Cart::clear();
    // It is now empty
    expect(Cart::isEmpty())->toBeTrue();

    // init the cart from serialized string
    Cart::unserialize($serialized);

    expect(Cart::subtotal())->toBe(90.0);
    expect(Cart::total())->toBe(90.0);
    expect(Cart::items()->count())->toBe(1);
    expect(Cart::quantity())->toBe(3);

    // add more item to be able to pass the condition rule
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 1,
        'price' => 30,
        'weight' => 1,
        'attributes' => [
            'color' => ['label' => 'Blue', 'value' => 'blue'],
            'size' => ['label' => 'Small', 'value' => 's'],
        ],
    ]);


    expect(Cart::items()->count())->toBe(1);
    expect(Cart::quantity())->toBe(4);
    // The quantity is not 4
    // And the subtotal is 120 ( 120 > 100 ). Rule returns true
    // It applies the -50% on subtotal
    // It applies the -50% on subtotal
    expect(Cart::subtotal())->toBe(60.0);
    expect(Cart::total())->toBe(60.0);


    // if we create a new condition on targets total value
    $condition2 = new Condition([
        'name' => 'Condition B',
        'type' => 'shipping',
        'target' => 'total',
    ]);

    $condition2->setActions([
        [
            'value' => '20',
            'rules' => new SerializableClosure(function() {
                // we can check an item quantity here..
                return Cart::quantity() < 5;
            }),
        ],
    ]);
    Cart::condition($condition2);

    expect(Cart::subtotal())->toBe(60.0);
    expect(Cart::total())->toBe(80.0);
});
