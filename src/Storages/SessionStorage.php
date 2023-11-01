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

    public function get($key)
    {
        return session()->get("$this->instance.$key");
    }

    public function put($key, $data)
    {
        session()->put("$this->instance.$key", $data);
    }
}
