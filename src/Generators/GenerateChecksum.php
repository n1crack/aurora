<?php

namespace Ozdemir\Aurora\Generators;

use Ozdemir\Aurora\Facades\Cart;

class GenerateChecksum
{
    public function __invoke(): string
    {
        return md5(serialize([
            Cart::items()->values()->pluck('quantity', 'hash'),
            Cart::total()->amount()
        ]));
    }
}
