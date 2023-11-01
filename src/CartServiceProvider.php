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

        $this->app->singleton(CartCalculatorCollection::class, function($app) {
            return new CartCalculatorCollection();
        });

        //        \Ozdemir\Aurora\Facades\Cart::calculateUsing(
        //            CartCalculator::SUBTOTAL,
        //            [
        //                Tax::class,
        //                ShippingClass::class
        //            ],
        //        );
        //
        //        \Ozdemir\Aurora\Facades\Cart::calculateUsing(
        //            CartCalculator::TOTAL,
        //            [
        //                Tax::class,
        //                ShippingClass::class
        //            ],
        //        );
    }
}
