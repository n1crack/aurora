<?php

namespace Ozdemir\Aurora\Contracts;

interface CartStorage
{
    /**
     * @param $instance
     */
    public function __construct($instance);

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null): mixed;

    /**
     * @param $key
     * @param $data
     * @return void
     */
    public function put($key, $data): void;
}
