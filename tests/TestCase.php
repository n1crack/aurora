<?php

namespace Ozdemir\Aurora\Tests;

use Illuminate\Events\Dispatcher;
use Orchestra\Testbench\TestCase as Orchestra;
use Ozdemir\Aurora\CartServiceProvider;
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
