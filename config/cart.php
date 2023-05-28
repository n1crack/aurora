<?php

return [

    'instance' => 'cart',

    'storage' => \Ozdemir\Aurora\Storage\SessionStorage::class,

    'cart_class' => \Ozdemir\Aurora\Cart::class,

    'cart_item' => \Ozdemir\Aurora\CartItem::class,

    'precision' => 2,

    'condition_order' => [
        'cart' => ['discount', 'other', 'shipping', 'coupon', 'tax'],
        'item' => ['discount', 'other', 'shipping', 'coupon', 'tax'],
    ],

];
