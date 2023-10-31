<?php

return [
    'instance' => 'cart',

    'cart_class' => \Ozdemir\Aurora\Cart::class,

    'storage' => [
        'class' => \Ozdemir\Aurora\Storages\SessionStorage::class,

        'session_key' => \Ozdemir\Aurora\DefaultSessionKey::class,
    ],

    'cache_store' => env('CART_STORE', config('cache.default')),

    'currency' => [

        'precision' => 2,
    ],
];
