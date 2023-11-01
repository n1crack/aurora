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
     * @return mixed
     */
    public function get($key);

    /**
     * @param $key
     * @param $data
     * @return mixed
     */
    public function put($key, $data);
}
