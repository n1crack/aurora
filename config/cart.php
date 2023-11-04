<?php

return [
    'instance' => 'cart',

    'cart_class' => \Ozdemir\Aurora\Cart::class,

    'storage' => [
        'class' => \Ozdemir\Aurora\Storages\SessionStorage::class,

        'session_key' => \Ozdemir\Aurora\Generators\DefaultSessionKey::class,
    ],

    'cache_store' => env('CART_STORE', config('cache.default')),

    'currency' => [
        'class' => \Ozdemir\Aurora\Money::class,

        'precision' => env('CART_CURRENCY_PRECISION', 2),
    ],

    'checksum_generator' => \Ozdemir\Aurora\Generators\CheckSumGenerator::class
];
