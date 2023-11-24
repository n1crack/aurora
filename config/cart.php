<?php

use Ozdemir\Aurora\Cart;
use Ozdemir\Aurora\Generators\GenerateChecksum;
use Ozdemir\Aurora\Generators\GenerateSessionKey;
use Ozdemir\Aurora\Storages\SessionStorage;

return [
    'instance' => 'cart',

    'cart_class' => Cart::class,

    'storage' => SessionStorage::class,

    'cache_store' => env('CART_STORE', config('cache.default')),

    'monetary' => [
        'class' => Money::class,

        'precision' => env('CART_CURRENCY_PRECISION', 2),
    ],

    'session_key_generator' => GenerateSessionKey::class,

    'checksum_generator' => GenerateChecksum::class,

    'calculate_using' => [
        //
    ]
];
