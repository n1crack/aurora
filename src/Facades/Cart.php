<?php

namespace Ozdemir\Cart\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ozdemir\Cart\Cart
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cart';
    }
}
