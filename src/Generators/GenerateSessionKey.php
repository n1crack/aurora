<?php

namespace Ozdemir\Aurora\Generators;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class GenerateSessionKey
{
    public function __invoke(): string
    {
        if (Auth::check()) {
            return 'user:' . Auth::id();
        }

        $guestToken = Cookie::get('cart_guest_token');

        if (!$guestToken) {
            $guestToken = uniqid();
            Cookie::queue('cart_guest_token', $guestToken, 1440);
        }

        return 'guest:' . $guestToken;
    }
}
