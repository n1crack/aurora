<?php

namespace Ozdemir\Aurora\Storages;

use Ozdemir\Aurora\Contracts\CartStorage;

class ArrayStorage implements CartStorage
{
    public string $instance;

    private $session = [];

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function get($key)
    {
        return $this->session["$this->instance.$key"] ?? null;
    }

    public function put($key, $data)
    {
        $this->session["$this->instance.$key"] = $data;
    }
}
