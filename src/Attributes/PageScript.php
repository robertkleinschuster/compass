<?php

namespace Compass\Attributes;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
readonly class PageScript
{
    public function __construct(private string $src)
    {
    }

    public static function __set_state(array $data): object
    {
        return new PageScript(
            src: $data['src']
        );
    }

    public function getSrc(): string
    {
        return $this->src;
    }
}