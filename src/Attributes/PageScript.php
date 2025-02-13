<?php

namespace Compass\Attributes;

use Attribute;
use Stringable;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
readonly class PageScript
{
    public function __construct(private string|Stringable $src)
    {
    }

    public function getSrc(): string
    {
        return (string)$this->src;
    }
}