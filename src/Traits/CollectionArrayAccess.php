<?php

namespace Ozdemir\Aurora\Traits;

trait CollectionArrayAccess
{
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    public function __get($name)
    {
        if ($this->isEmpty() || !$this->has($name)) {
            return null;
        }

        return $this[$name];
    }

    public function __set(string $name, $value): void
    {
        $this[$name] = $value;
    }
}
