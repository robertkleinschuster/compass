<?php

namespace Compass\Attributes;

use Attribute;
use JsonSerializable;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
readonly class Stylesheet implements JsonSerializable
{
    public function __construct(private string $file, private string $media = 'all')
    {
    }

    /**
     * @param array<string, string> $data
     * @return Stylesheet
     */
    public static function __set_state(array $data): Stylesheet
    {
        return new Stylesheet(
            file: $data['file'],
            media: $data['media']
        );
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getMedia(): string
    {
        return $this->media;
    }

    public function getHref(): string
    {
        $hash = hash_file('crc32c', $this->file);
        return "/static/$hash.css";
    }

    public function jsonSerialize(): array
    {
        return [
            'href' => $this->getHref(),
            'media' => $this->getMedia(),
        ];
    }
}