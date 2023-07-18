<?php

namespace Ozdemir\Aurora\Storage;

class CacheStorage implements StorageInterface
{
    public string $instance;

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function get($key)
    {
        return cache()->store(config('cart.cache_store'))->get("$this->instance.$key");
    }

    public function put($key, $data)
    {
        cache()->store(config('cart.cache_store'))->put("$this->instance.$key", $data);
    }
}
