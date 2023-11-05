<?php

namespace Ozdemir\Aurora\Generators;

use Illuminate\Support\Facades\Auth;

class GenerateSessionKey
{
    public function __invoke(): string
    {
        return Auth::check() ? 'user:' . Auth::id() : 'guest:' . uniqid();
    }
}
