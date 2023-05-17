<?php

use Laravel\SerializableClosure\SerializableClosure;
use Ozdemir\Aurora\Facades\Cart;

it('can serialize the current', function() {
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

    $condition = new Ozdemir\Aurora\Condition([
        'name' => 'Condition',
        'type' => 'tax',
        'target' => 'subtotal',
    ]);
    $condition->setActions([
        [
            'value' => '125', 'rules' => serialize(new SerializableClosure(fn() => Cart::subtotal() < 1000)),
        ],
    ]);
    Cart::condition($condition);

    expect(Cart::items())->toHaveCount(1);
    expect(Cart::serialize())->toBeString();
});

it('can unserialize from serialized string', function() {
    expect(Cart::isEmpty())->toBeTrue();

    $serialized = 'a:5:{i:0;O:29:"Ozdemir\Aurora\CartCollection":2:{s:8:" * items";a:3:{s:32:"6c89c03dc3e467c829a36005fdd4b387";O:23:"Ozdemir\Aurora\CartItem":3:{s:8:" * items";a:9:{s:2:"id";i:2;s:4:"name";s:7:"Numquam";s:8:"quantity";i:1;s:5:"price";d:612;s:6:"weight";i:0;s:3:"sku";s:1:"m";s:10:"attributes";O:29:"Illuminate\Support\Collection":2:{s:8:" * items";a:1:{i:0;O:28:"Ozdemir\Aurora\CartAttribute":2:{s:8:" * items";a:3:{s:5:"value";s:1:"m";s:5:"label";s:4:"Size";s:5:"price";s:2:"+5";}s:28:" * escapeWhenCastingToString";b:0;}}s:28:" * escapeWhenCastingToString";b:0;}s:10:"conditions";O:34:"Ozdemir\Aurora\ConditionCollection":2:{s:8:" * items";a:1:{i:0;O:24:"Ozdemir\Aurora\Condition":5:{s:4:"name";s:8:"Tax (4%)";s:6:"target";s:8:"subtotal";s:4:"type";s:3:"tax";s:7:"actions";a:1:{i:0;a:2:{s:5:"value";s:2:"4%";s:5:"rules";a:0:{}}}s:5:"value";d:24.68;}}s:28:" * escapeWhenCastingToString";b:0;}s:4:"hash";s:32:"6c89c03dc3e467c829a36005fdd4b387";}s:28:" * escapeWhenCastingToString";b:0;s:22:" * itemConditionsOrder";a:4:{i:0;s:8:"discount";i:1;s:5:"other";i:2;s:3:"tax";i:3;s:8:"shipping";}}s:32:"3c667f916debde81e642c3409349a9c2";O:23:"Ozdemir\Aurora\CartItem":3:{s:8:" * items";a:9:{s:2:"id";i:4;s:4:"name";s:11:"Consectetur";s:8:"quantity";i:3;s:5:"price";d:617;s:6:"weight";i:0;s:3:"sku";s:1:"m";s:10:"attributes";O:29:"Illuminate\Support\Collection":2:{s:8:" * items";a:1:{i:0;O:28:"Ozdemir\Aurora\CartAttribute":2:{s:8:" * items";a:3:{s:5:"value";s:1:"m";s:5:"label";s:4:"Size";s:5:"price";s:2:"+5";}s:28:" * escapeWhenCastingToString";b:0;}}s:28:" * escapeWhenCastingToString";b:0;}s:10:"conditions";O:34:"Ozdemir\Aurora\ConditionCollection":2:{s:8:" * items";a:1:{i:0;O:24:"Ozdemir\Aurora\Condition":5:{s:4:"name";s:8:"Tax (4%)";s:6:"target";s:8:"subtotal";s:4:"type";s:3:"tax";s:7:"actions";a:1:{i:0;a:2:{s:5:"value";s:2:"4%";s:5:"rules";a:0:{}}}s:5:"value";d:74.64;}}s:28:" * escapeWhenCastingToString";b:0;}s:4:"hash";s:32:"3c667f916debde81e642c3409349a9c2";}s:28:" * escapeWhenCastingToString";b:0;s:22:" * itemConditionsOrder";a:4:{i:0;s:8:"discount";i:1;s:5:"other";i:2;s:3:"tax";i:3;s:8:"shipping";}}s:32:"cc8fb1b4839c52a5bc04b185523f87ca";O:23:"Ozdemir\Aurora\CartItem":3:{s:8:" * items";a:9:{s:2:"id";i:7;s:4:"name";s:7:"Quaerat";s:8:"quantity";i:1;s:5:"price";d:1245;s:6:"weight";i:0;s:3:"sku";s:2:"lg";s:10:"attributes";O:29:"Illuminate\Support\Collection":2:{s:8:" * items";a:1:{i:0;O:28:"Ozdemir\Aurora\CartAttribute":2:{s:8:" * items";a:3:{s:5:"value";s:2:"lg";s:5:"label";s:4:"Size";s:5:"price";s:3:"+10";}s:28:" * escapeWhenCastingToString";b:0;}}s:28:" * escapeWhenCastingToString";b:0;}s:10:"conditions";O:34:"Ozdemir\Aurora\ConditionCollection":2:{s:8:" * items";a:1:{i:0;O:24:"Ozdemir\Aurora\Condition":5:{s:4:"name";s:8:"Tax (4%)";s:6:"target";s:8:"subtotal";s:4:"type";s:3:"tax";s:7:"actions";a:1:{i:0;a:2:{s:5:"value";s:2:"4%";s:5:"rules";a:0:{}}}s:5:"value";d:50.2;}}s:28:" * escapeWhenCastingToString";b:0;}s:4:"hash";s:32:"cc8fb1b4839c52a5bc04b185523f87ca";}s:28:" * escapeWhenCastingToString";b:0;s:22:" * itemConditionsOrder";a:4:{i:0;s:8:"discount";i:1;s:5:"other";i:2;s:3:"tax";i:3;s:8:"shipping";}}}s:28:" * escapeWhenCastingToString";b:0;}i:1;O:34:"Ozdemir\Aurora\ConditionCollection":2:{s:8:" * items";a:2:{s:8:"VAT (6%)";O:24:"Ozdemir\Aurora\Condition":5:{s:4:"name";s:8:"VAT (6%)";s:6:"target";s:8:"subtotal";s:4:"type";s:3:"tax";s:7:"actions";a:2:{s:5:"value";s:2:"6%";s:5:"rules";a:0:{}}s:5:"value";d:227.25120000000004;}s:8:"Coupon 1";O:24:"Ozdemir\Aurora\Condition":5:{s:4:"name";s:8:"Coupon 1";s:6:"target";s:8:"subtotal";s:4:"type";s:5:"other";s:7:"actions";a:1:{i:0;a:2:{s:5:"value";s:4:"-100";s:5:"rules";a:0:{}}}s:5:"value";d:-100;}}s:28:" * escapeWhenCastingToString";b:0;}i:2;a:4:{i:0;s:8:"discount";i:1;s:5:"other";i:2;s:3:"tax";i:3;s:8:"shipping";}i:3;a:4:{i:0;s:8:"discount";i:1;s:5:"other";i:2;s:3:"tax";i:3;s:8:"shipping";}i:4;O:37:"Ozdemir\Aurora\Storage\SessionStorage":1:{s:8:"instance";s:4:"cart";}}';

    Cart::unserialize($serialized);

    expect(Cart::items())->toHaveCount(3);
    expect(Cart::quantity())->toBe(5);

    expect(round(Cart::subtotal(), 2))->toBe(3887.52);
    expect(round(Cart::total(), 2))->toBe(4014.77);

    expect(Cart::conditions())->toHaveCount(2);
    expect(Cart::conditions()->first())->toBeInstanceOf(\Ozdemir\Aurora\Condition::class);
    expect(Cart::conditions()->first()->name)->toBe('Coupon 1');
});
