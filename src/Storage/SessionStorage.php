<?php

namespace Ozdemir\Cart\Storage;

class SessionStorage implements StorageInterface
{
    private $instance;

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
