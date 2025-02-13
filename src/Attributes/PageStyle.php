<?php

namespace Compass\Attributes;

use Attribute;
use Stringable;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
readonly class PageStyle
{
    public function __construct(private string|Stringable $href)
    {
    }

    public function getHref(): string
    {
        return (string)$this->href;
    }
}