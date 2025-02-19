<?php

namespace Compass\Attributes;

use Attribute;

#[Attribute]
class Resource
{
    /**
     * @param array<string, mixed> $data
     * @return Resource
     */
    public static function __set_state(array $data): Resource
    {
        return new Resource();
    }
}