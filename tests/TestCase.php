<?php

namespace Ozdemir\Aurora\Tests;

use Closure;
use Orchestra\Testbench\TestCase as Orchestra;
use Ozdemir\Aurora\Cart;
use Ozdemir\Aurora\CartServiceProvider;
use Ozdemir\Aurora\Money;
use Ozdemir\Aurora\Storages\ArrayStorage;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton('cart', function($app) {
            $cartClass = config('cart.cart_class');

            //  $storageClass = config('cart.storage');
            //  /* @var StorageInterface $storage */
            //  $storage = new $storageClass('cart');
            $storage = new ArrayStorage('cart');

            return new $cartClass($storage);
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            CartServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}

/* @noinspection */

class TaxExample
{
    public function handle($payload, Closure $next)
    {
        /* @var Money $price */
        [$price, $breakdowns] = $payload;

        $taxPrice = new Money(15);

        $price = $price->add($taxPrice);

        $breakdowns[] = ['label' => 'Tax', 'value' => $taxPrice];

        return $next([$price, $breakdowns]);
    }
}

/* @noinspection */

class ShippingExample
{
    public function handle($payload, Closure $next)
    {
        /* @var Cart $cart */
        /* @var Money $price */
        [$price, $breakdowns] = $payload;

        $shippingCost = new Money(10);

        $price = $price->add($shippingCost);

        $breakdowns[] = ['label' => 'Shipping', 'value' => $shippingCost];

        return $next([$price, $breakdowns]);
    }
}

/* @noinspection */

class DiscountExample
{
    public function handle($payload, Closure $next)
    {
        /* @var Money $price */
        [$price, $breakdowns] = $payload;
        //
        //        $total = \Ozdemir\Aurora\Calculator::skip($this, function() {
        //            return \Ozdemir\Aurora\Facades\Cart::total()->amount();
        //        });
        //
        //        dump($total);

        $discountPrice = new Money($price->multiply(5 / 100)->amount());

        $price = $price->subtract($discountPrice);

        $breakdowns[] = ['label' => 'Discount', 'value' => $discountPrice->multiply(-1)];

        return $next([$price, $breakdowns]);
    }
}
