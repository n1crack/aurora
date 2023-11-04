<?php

namespace Ozdemir\Aurora\Storages;

use Ozdemir\Aurora\Contracts\CartStorage;

class SessionStorage implements CartStorage
{
    public string $instance;

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function get($key, $default = null): mixed
    {
        return session()->get("$this->instance.$key", $default);
    }

    public function put($key, $data): void
    {
        session()->put("$this->instance.$key", $data);
    }
}
