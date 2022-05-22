<?php

namespace Ozdemir\Cart\Storage;

class ArrayStorage implements StorageInterface
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
