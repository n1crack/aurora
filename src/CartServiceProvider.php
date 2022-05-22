<?php

namespace Ozdemir\Cart;

use Illuminate\Events\Dispatcher;
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

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/cart.php' => config_path('cart.php'),
        ]);

        $this->app->bind(Cart::class, function($app) {
            $defaultInstance = config('cart.instance');

            return $app[$defaultInstance];
        });
    }
}
