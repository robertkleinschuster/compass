<?php

namespace Compass\Attributes;

use Attribute;

#[Attribute]
class Resource
{
    public static function __set_state(array $data): object
    {
        return new Resource();
    }
}