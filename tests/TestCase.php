<?php

namespace Ozdemir\Cart\Tests;

use Illuminate\Events\Dispatcher;
use Orchestra\Testbench\TestCase as Orchestra;
use Ozdemir\Cart\Cart;
use Ozdemir\Cart\CartServiceProvider;
use Ozdemir\Cart\Storage\ArrayStorage;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton('laravel-cart', function($app) {
            $storage = new ArrayStorage('cart');

            return new Cart($storage, new Dispatcher(), config('cart') ?? []);
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
