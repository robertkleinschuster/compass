<?php

declare(strict_types=1);

namespace Compass\Attributes;

use Attribute;

#[Attribute]
class Reactive
{
    public static function __set_state(array $data): object
    {
        return new Reactive();
    }
}