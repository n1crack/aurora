<?php

namespace Ozdemir\Aurora\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ozdemir\Aurora\Cart
 */
class Cart extends Facade
{
    /**
     * Class Cart
     *
     * Facade for the Cart class in the Ozdemir\Aurora namespace.
     *
     * @package Ozdemir\Aurora
     *
     * @mixin \Ozdemir\Aurora\Cart
     * @see \Ozdemir\Aurora\Cart
     *
     */
    protected static function getFacadeAccessor()
    {
        return 'cart';
    }
}
