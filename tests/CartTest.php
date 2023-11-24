<?php

use Illuminate\Support\Collection;
use Ozdemir\Aurora\CartItem;
use Ozdemir\Aurora\Facades\Cart;

it('is empty as default', function() {
    expect(Cart::isEmpty())->toBeTrue();
});

it('is can get instance key for a guest user', function() {
    expect(Cart::getInstanceKey())->toBe('cart');
});

it('is can get instance key for a logged-in user', function() {
    $user = (new \Illuminate\Foundation\Auth\User())->forceFill([
        'id' => 123,
        'name' => 'User name',
        'email' => 'email@example.com',
    ]);

    expect(Auth::check())->toBe(false);
    \Illuminate\Support\Facades\Auth::login($user);

    expect(Auth::id())->toBe($user->id)
        ->and(Auth::check())->toBe(true)
        ->and(Cart::getSessionKey())->toBe('user:123')
        ->and(Cart::getInstanceKey())->toBe('cart');
});


it('can add items to Cart', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 3;
    $product->price = 30;

    Cart::add(
        new CartItem($product, quantity: 1),
    );

    expect(Cart::quantity())->toBe(1)
        ->and(Cart::total())->toBe(30.0)
        ->and(Cart::isEmpty())->toBeFalse();
});

it('can add multiple items to Cart', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 3;
    $product->price = 30;

    $product2 = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product2->id = 4;
    $product2->price = 179;

    Cart::add(
        new CartItem($product, 1),
        new CartItem($product2, 2),
    );

    expect(Cart::items())->toHaveCount(2)
        ->and(Cart::quantity())->toBe(3);
});

it('increase items count if the id is same', function() {

    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 3;
    $product->price = 30;

    Cart::add(
        new CartItem($product, quantity: 1),
    );

    Cart::add(
        new CartItem($product, quantity: 1),
    );

    expect(Cart::items())->toHaveCount(1)
        ->and(Cart::quantity())->toBe(2);
});

it('can have options and options have to be instance of CartItemOption Class', function() {

    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 1;
    $product->price = 30;

    Cart::add(
        (new CartItem($product, quantity: 1))
            ->withOption('color', 'red')
            ->withOption('size', 'large')
    );

    expect(Cart::quantity())->toBe(1)
        ->and(Cart::items()->first()->options)->toBeInstanceOf(Collection::class)
        ->and(Cart::items()->first()->options)->toHaveCount(2)
        ->and(Cart::items()->first()->options->first())->toBeInstanceOf(\Ozdemir\Aurora\CartItemOption::class)
        ->and(Cart::items()->first()->options->first()->value)->toBe('red');
});

it('can add new item instance with the same item id if the item has different options', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 1;
    $product->price = 30;
    $product->weight = 0;

    Cart::add(
        (new CartItem($product, quantity: 1))
            ->withOption('color', 'red')
            ->withOption('size', 'large')
    );

    Cart::add(
        (new CartItem($product, quantity: 1))
            ->withOption('color', 'blue')
            ->withOption('size', 's')
    );

    expect(Cart::items())->toHaveCount(2)
        ->and(Cart::quantity())->toBe(2)
        ->and(Cart::weight())->toBe(0);
});

it('can sum total cart item prices', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 1;
    $product->price = 30;
    $product->weight = 1;

    Cart::add(
        (new CartItem($product, quantity: 2))
    );

    $product2 = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product2->id = 2;
    $product2->price = 470;
    $product2->weight = 8;

    Cart::add(
        (new CartItem($product2, quantity: 1))
    );

    expect(Cart::items())->toHaveCount(2)
        ->and(Cart::total())->toBe(530.0)
        ->and(Cart::subtotal())->toBe(530.0)
        ->and(Cart::weight())->toBe(10);
});

it('can clear the cart', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 1;
    $product->price = 30;
    $product->weight = 1;

    Cart::add(new CartItem($product, quantity: 2));

    expect(Cart::items())->toHaveCount(1)
        ->and(Cart::quantity())->toBe(2)
        ->and(Cart::weight())->toBe(2);

    Cart::clear();

    expect(Cart::isEmpty())->toBeTrue()
        ->and(Cart::items())->toHaveCount(0)
        ->and(Cart::total())->toBe(0.0)
        ->and(Cart::weight())->toBe(0);
});

it('can remove item from the cart', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 1;
    $product->price = 30;
    $product->weight = 1;

    Cart::add(new CartItem($product, 2));

    $product2 = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product2->id = 2;
    $product2->price = 470;
    $product2->weight = 8;

    Cart::add(new CartItem($product2, 1));

    expect(Cart::items())->toHaveCount(2)
        ->and(Cart::total())->toBe(530.0)
        ->and(Cart::quantity())->toBe(3)
        ->and(Cart::weight())->toBe(10);

    $cartItem = Cart::items()->last();

    Cart::remove($cartItem->hash());

    expect(Cart::items())->toHaveCount(1)
        ->and(Cart::total())->toBe(60.0)
        ->and(Cart::subtotal())->toBe(60.0)
        ->and(Cart::quantity())->toBe(2)
        ->and(Cart::weight())->toBe(2);
});

it('can sync the items', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 1;
    $product->price = 30;
    $product->weight = 1;

    $product2 = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product2->id = 2;
    $product2->price = 470;
    $product2->weight = 8;

    Cart::add(
        new CartItem($product, 2),
        new CartItem($product2, 1)
    );

    expect(Cart::items())->toHaveCount(2)
        ->and(Cart::total())->toBe(530.0)
        ->and(Cart::quantity())->toBe(3)
        ->and(Cart::weight())->toBe(10);

    $product->weight = 5;
    $product->price = 630;
    $product2->weight = 1;
    $product2->price = 25;

    Cart::sync(
        new CartItem($product, 2),
        new CartItem($product2, 10)
    );

    expect(Cart::items())->toHaveCount(2)
        ->and(Cart::total())->toBe(1510.0)
        ->and(Cart::quantity())->toBe(12)
        ->and(Cart::weight())->toBe(20);
});

it('can update the quantity', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 1;
    $product->price = 30;
    $product->weight = 1;

    Cart::add(new CartItem($product, 2));

    $item = Cart::items()->first();

    $updatedCartItem = Cart::update($item->hash(), 5);

    expect($item->quantity)->toBe(5)
        ->and(Cart::items())->toHaveCount(1)
        ->and(Cart::quantity())->toBe(5)
        ->and($updatedCartItem)->toBeInstanceOf(CartItem::class);
});

it('can get items from hash', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 3;
    $product->price = 30;
    $product->weight = 1;

    Cart::add(
        (new CartItem($product, 3))
            ->withOption('color', 'blue')
            ->withOption('size', 's')
    );

    $item = Cart::items()->first();

    expect(Cart::item($item->hash))->toBeInstanceOf(\Ozdemir\Aurora\CartItem::class)
        ->and(Cart::item($item->hash)->product->id)->toBe(3)
        ->and(Cart::item($item->hash)->quantity)->toBe(3)
        ->and(Cart::item($item->hash)->unitPrice())->toBe(30.0)
        ->and(Cart::item($item->hash)->weight())->toBe(3)
        ->and(Cart::item($item->hash)->options)->toBeInstanceOf(Collection::class)
        ->and(Cart::item($item->hash)->options)->toHaveCount(2)
        ->and(Cart::item($item->hash)->options->first())->toBeInstanceOf(\Ozdemir\Aurora\CartItemOption::class)
        ->and(Cart::item($item->hash)->options->first()->label)->toBe('color')
        ->and(Cart::item($item->hash)->options->first()->value)->toBe('blue')
        ->and(Cart::item($item->hash)->options->last()->label)->toBe('size');
});

it('can initialize a new instance', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 2;
    $product->price = 30;
    $product->weight = 4;

    Cart::add(new CartItem($product, 2));

    expect(Cart::items())->toHaveCount(1);

    $wishlist = Cart::make(new \Ozdemir\Aurora\Storages\ArrayStorage('wishlist'));

    expect($wishlist->items())->toHaveCount(0);

    $product2 = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product2->id = 3;
    $product2->price = 20;
    $product2->weight = 5;

    $wishlist->add(new CartItem($product2, 100));

    expect($wishlist->items())->toHaveCount(1)
        ->and($wishlist->quantity())->toBe(100)
        ->and($wishlist->weight())->toBe(500)
        ->and($wishlist->getInstanceKey())->toBe('wishlist')
        ->and(Cart::items())->toHaveCount(1)
        ->and(Cart::quantity())->toBe(2)
        ->and(Cart::weight())->toBe(8)
        ->and(Cart::getInstanceKey())->toBe('cart');
});


it('can refresh user id after login', function() {

    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 2;
    $product->price = 30;
    $product->weight = 4;

    Cart::add(new CartItem($product, 2));

    expect(Cart::items())->toHaveCount(1)
        ->and(Cart::quantity())->toBe(2)
        ->and(Cart::weight())->toBe(8)
        ->and(Cart::getInstanceKey())->toBe('cart');

    $user = (new \Illuminate\Foundation\Auth\User())->forceFill([
        'id' => 123,
        'name' => 'Their name',
        'email' => 'email@example.com',
    ]);

    expect(Auth::check())->toBe(false);
    \Illuminate\Support\Facades\Auth::login($user);

    expect(Auth::id())->toBe($user->id);
    expect(Auth::check())->toBe(true);

    Cart::loadSession('user:' . Auth::id());

    expect(Cart::getSessionKey())->toBe('user:' . Auth::id())
        ->and(Cart::items())->toHaveCount(0)
        ->and(Cart::quantity())->toBe(0)
        ->and(Cart::weight())->toBe(0)
        ->and(Cart::total())->toBe(0.0)
        ->and(Cart::getInstanceKey())->toBe('cart');

});


it('can load any session Cart', function() {
    $product = new \Ozdemir\Aurora\Tests\Stubs\Models\Product();
    $product->id = 2;
    $product->price = 30;
    $product->weight = 4;

    Cart::add(new CartItem($product, 2));

    expect(Cart::items())->toHaveCount(1)
        ->and(Cart::quantity())->toBe(2)
        ->and(Cart::weight())->toBe(8)
        ->and(Cart::getInstanceKey())->toBe('cart')
        ->and(Cart::getSessionKey())->toContain('guest:');

    $user = (new \Illuminate\Foundation\Auth\User())->forceFill([
        'id' => 123,
        'name' => 'Their name',
        'email' => 'email@example.com',
    ]);

    $oldSession = Cart::getSessionKey();

    expect(Auth::check())->toBe(false);
    \Illuminate\Support\Facades\Auth::login($user);

    expect(Auth::id())->toBe($user->id);
    expect(Auth::check())->toBe(true);

    Cart::loadSession('user:' . Auth::id());

    expect(Cart::getSessionKey())->toBe('user:' . Auth::id())
        ->and(Cart::items())->toHaveCount(0)
        ->and(Cart::quantity())->toBe(0)
        ->and(Cart::weight())->toBe(0)
        ->and(Cart::total())->toBe(0.0)
        ->and(Cart::getInstanceKey())->toBe('cart');

    Cart::loadSession($oldSession);

    expect(Cart::items())->toHaveCount(1)
        ->and(Cart::quantity())->toBe(2)
        ->and(Cart::weight())->toBe(8)
        ->and(Cart::getInstanceKey())->toBe('cart');
});
