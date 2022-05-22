<?php

use Illuminate\Support\Collection;
use Ozdemir\Cart\CartAttribute;
use Ozdemir\Cart\Facades\Cart;

it('is empty as default', function() {
    expect(Cart::isEmpty())->toBeTrue();
});

it('can add items to Cart', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 1,
        'price' => 30,
    ]);

    expect(Cart::quantity())->toBe(1);
    expect(Cart::isEmpty())->toBeFalse();
});

it('can add multiple items to Cart', function() {
    Cart::add([
        [
            'id' => 'tshirt',
            'name' => 'T-Shirt',
            'quantity' => 1,
            'price' => 30,
        ],
        [
            'id' => 'headphones',
            'name' => 'Headphones',
            'quantity' => 2,
            'price' => 179,
        ],
    ]);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::quantity())->toBe(3);
});

it('increase items count if the id is same', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 1,
        'price' => 30,
    ]);

    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 1,
        'price' => 30,
    ]);

    expect(Cart::quantity())->toBe(2);
});

it('can add new item instance with the same item id if the item has a stock keeping unit', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 1,
        'price' => 30,
        'sku' => 'tshirt-blue-small',
    ]);

    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 1,
        'price' => 30,
        'sku' => 'tshirt-red-large',
    ]);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::quantity())->toBe(2);
});

it('can have attribute and attribute have to be instance of CartAttribute Class', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 1,
        'price' => 30,
        'attributes' => [
            'color' => ['label' => 'Red', 'value' => 'red'],
            'size' => ['label' => 'Large', 'value' => 'l'],
        ],
    ]);

    expect(Cart::quantity())->toBe(1);
    expect(Cart::items()->first()->attributes)->toBeInstanceOf(Collection::class);
    expect(Cart::items()->first()->attributes)->toHaveCount(2);
    expect(Cart::items()->first()->attributes->first())->toBeInstanceOf(CartAttribute::class);
    expect(Cart::items()->first()->attributes->first()->get('value'))->toBe('red');
});

it('can add new item instance with the same item id if the item has different attributes without sku', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 1,
        'price' => 30,
        'attributes' => [
            'color' => ['label' => 'Red', 'value' => 'red'],
            'size' => ['label' => 'Large', 'value' => 'l'],
        ],
    ]);

    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 1,
        'price' => 30,
        'attributes' => [
            'color' => ['label' => 'Blue', 'value' => 'blue'],
            'size' => ['label' => 'Small', 'value' => 's'],
        ],
    ]);
    expect(Cart::items())->toHaveCount(2);
    expect(Cart::quantity())->toBe(2);
    expect(Cart::weight())->toBe(0);
});

it('can sum total cart item prices', function() {
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
    expect(Cart::total())->toBe(530);
    expect(Cart::subtotal())->toBe(530);
    expect(Cart::weight())->toBe(10);
});

it('can clear cart ', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 2,
        'price' => 30,
        'weight' => 1,
    ]);

    expect(Cart::items())->toHaveCount(1);
    expect(Cart::quantity())->toBe(2);
    expect(Cart::weight())->toBe(2);

    Cart::clear();

    expect(Cart::isEmpty())->toBeTrue();
    expect(Cart::items())->toHaveCount(0);
    expect(Cart::total())->toBe(0);
    expect(Cart::weight())->toBe(0);
});

it('can remove item from the cart', function() {
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
    expect(Cart::total())->toBe(530);
    expect(Cart::quantity())->toBe(3);
    expect(Cart::weight())->toBe(10);

    $item = Cart::items()->last();
    Cart::remove($item->hash());

    expect(Cart::items())->toHaveCount(1);
    expect(Cart::total())->toBe(60);
    expect(Cart::subtotal())->toBe(60);
    expect(Cart::quantity())->toBe(2);
    expect(Cart::weight())->toBe(2);
});

it('can sync the items', function() {
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
    expect(Cart::total())->toBe(530);
    expect(Cart::quantity())->toBe(3);
    expect(Cart::weight())->toBe(10);

    Cart::sync([
        [
            'id' => 'tv',
            'name' => 'Television',
            'quantity' => 2,
            'price' => 630,
            'weight' => 5,
        ], [
            'id' => 'tshirt',
            'name' => 'T-Shirt',
            'quantity' => 10,
            'price' => 25,
            'weight' => 1,
        ],
    ]);

    expect(Cart::items())->toHaveCount(2);
    expect(Cart::total())->toBe(1510);
    expect(Cart::quantity())->toBe(12);
    expect(Cart::weight())->toBe(20);
});

it('can update the items', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 2,
        'price' => 30,
        'weight' => 1,
    ]);

    $item = Cart::items()->first();

    Cart::update($item->hash(), [
        'id' => 'tshirt',
        'name' => 'Updated T-Shirt',
        'quantity' => 5,
        'price' => 35,
        'weight' => 0.5,
    ]);

    expect($item->name)->toBe('Updated T-Shirt');
    expect($item->quantity)->toBe(5);
    expect($item->weight())->toBe(0.5);
    expect($item->price())->toBe(35);

    expect(Cart::items())->toHaveCount(1);
    expect(Cart::quantity())->toBe(5);
});

it('can update only quantity if the second param is integer', function() {
    Cart::add([
        'id' => 'tshirt',
        'name' => 'T-Shirt',
        'quantity' => 2,
        'price' => 30,
        'weight' => 1,
    ]);

    $item = Cart::items()->first();

    expect($item->quantity)->toBe(2);
    expect(Cart::items())->toHaveCount(1);

    Cart::update($item->hash(), 20);

    expect($item->quantity)->toBe(20);
    expect(Cart::total())->toBe(600);
    expect(Cart::items())->toHaveCount(1);
});

it('can get items from hash', function() {
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

    $item = Cart::items()->first();

    expect(Cart::item($item->hash))->toBeInstanceOf(\Ozdemir\Cart\CartItem::class);
    expect(Cart::item($item->hash)->id)->toBe('tshirt');
    expect(Cart::item($item->hash)->name)->toBe('T-Shirt');
    expect(Cart::item($item->hash)->quantity)->toBe(3);
    expect(Cart::item($item->hash)->price)->toBe(30);
    expect(Cart::item($item->hash)->weight)->toBe(1);
    expect(Cart::item($item->hash)->attributes)->toBeInstanceOf(Collection::class);
    expect(Cart::item($item->hash)->attributes)->toHaveCount(2);
    expect(Cart::item($item->hash)->attributes->first())->toBeInstanceOf(CartAttribute::class);
    expect(Cart::item($item->hash)->attributes->first()->label)->toBe('Blue');
    expect(Cart::item($item->hash)->attributes->last()->label)->toBe('Small');
});
