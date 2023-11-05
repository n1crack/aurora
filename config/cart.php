<?php

return [
    'instance' => 'cart',

    'cart_class' => \Ozdemir\Aurora\Cart::class,

    'storage' => \Ozdemir\Aurora\Storages\SessionStorage::class,

    'cache_store' => env('CART_STORE', config('cache.default')),

    'currency' => [
        'class' => \Ozdemir\Aurora\Money::class,

        'precision' => env('CART_CURRENCY_PRECISION', 2),
    ],

    'session_key_generator' => \Ozdemir\Aurora\Generators\GenerateSessionKey::class,

    'checksum_generator' => \Ozdemir\Aurora\Generators\GenerateChecksum::class
];
