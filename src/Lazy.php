<?php

declare(strict_types=1);

namespace Compass;

use Attribute;

#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_CLASS)]
class Lazy
{
    public function __construct(public mixed $loading = null)
    {
    }
}