<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\ServiceProvider;
use Ozdemir\Aurora\Storages\StorageInterface;

class CartServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/cart.php',
            'cart'
        );

        $defaultInstance = config('cart.instance');

        $this->app->singleton($defaultInstance, function($app) use ($defaultInstance) {
            $cartClass = config('cart.cart_class');

            $storageClass = config('cart.storage');

            /* @var StorageInterface $storage */
            $storage = new $storageClass($defaultInstance);

            return new $cartClass($storage);
        });
    }
}
