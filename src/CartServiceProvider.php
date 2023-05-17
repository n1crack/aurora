<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cart.php',
            'cart'
        );

        $defaultInstance = config('cart.instance');

        $this->app->singleton($defaultInstance, function($app) use ($defaultInstance) {
            $storageClass = config('cart.storage');

            $storage = new $storageClass($defaultInstance);

            $dispatcher = $app->get('events');

            return new Cart($storage, $dispatcher, config('cart') ?? []);
        });
    }
}
