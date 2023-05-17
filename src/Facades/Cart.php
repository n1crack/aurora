<?php

namespace Ozdemir\Aurora\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ozdemir\Aurora\Cart
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cart';
    }
}
