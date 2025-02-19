<?php

declare(strict_types=1);

namespace Compass\Attributes;

use Attribute;

#[Attribute]
class Reactive
{
    /**
     * @param array<string, mixed> $data
     * @return Reactive
     */
    public static function __set_state(array $data): Reactive
    {
        return new Reactive();
    }
}