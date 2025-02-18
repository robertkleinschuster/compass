<?php

namespace Compass\Attributes;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
readonly class Script
{
    public function __construct(private string $src, private bool $module = true)
    {
    }

    public static function __set_state(array $data): object
    {
        return new Script(
            src: $data['src'],
            module: $data['module']
        );
    }

    public function getSrc(): string
    {
        return $this->src;
    }

    public function isModule(): bool
    {
        return $this->module;
    }
}