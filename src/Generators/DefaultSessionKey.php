<?php

namespace Ozdemir\Aurora\Generators;

use Illuminate\Support\Facades\Auth;

class DefaultSessionKey
{
    public function __invoke(): string
    {
        return Auth::check() ? 'user:' . Auth::id() : 'guest:' . uniqid();
    }
}
