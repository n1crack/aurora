<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\ServiceProvider;
use Ozdemir\Aurora\Contracts\CartStorage;

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

            /* @var CartStorage $storage */
            $storage = new $storageClass($defaultInstance);

            return new $cartClass($storage);
        });

        $this->app->singleton(Calculator::class, function($app) {
            return new Calculator();
        });
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/cart.php' => config_path('cart.php'),
        ]);
    }
}
