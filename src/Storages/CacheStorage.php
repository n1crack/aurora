<?php

namespace Ozdemir\Aurora\Storages;

use Ozdemir\Aurora\Contracts\CartStorage;

class CacheStorage implements CartStorage
{
    public string $instance;

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function get($key, $default = null): mixed
    {
        return cache()->store(config('cart.cache_store'))->get("$this->instance.$key", $default);
    }

    public function put($key, $data): void
    {
        cache()->store(config('cart.cache_store'))->put("$this->instance.$key", $data);
    }
}
