<?php

namespace Compass\Attributes;

use Attribute;
use JsonSerializable;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
readonly class Script implements JsonSerializable
{
    public function __construct(private string $file, private bool $module = true)
    {
    }

    /**
     * @param array<string, mixed> $data
     * @return Script
     */
    public static function __set_state(array $data): Script
    {
        return new Script(
            file: $data['file'],
            module: $data['module']
        );
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function isModule(): bool
    {
        return $this->module;
    }

    public function getSrc(): string
    {
        $hash = hash_file('crc32c', $this->file);
        return "/static/$hash.js";
    }

    public function jsonSerialize(): array
    {
        return [
            'src' => $this->getSrc(),
            'module' => $this->isModule(),
        ];
    }


}