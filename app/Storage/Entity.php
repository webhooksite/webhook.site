<?php

namespace App\Storage;

use Illuminate\Contracts\Support\Arrayable;

abstract class Entity implements Arrayable
{
    private $attributes = [];

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function __get($name)
    {
        return $this->attributes[$name];
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function attributes()
    {
        return $this->attributes;
    }

    public function toArray()
    {
        return $this->attributes;
    }
}