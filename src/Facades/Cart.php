<?php

namespace Ozdemir\Aurora\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Cart
 *
 * Facade for the Cart class in the Ozdemir\Aurora namespace.
 *
 * @package Ozdemir\Aurora
 *
 * @mixin \Ozdemir\Aurora\Cart
 *
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cart';
    }
}
