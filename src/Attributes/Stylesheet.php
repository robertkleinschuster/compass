<?php

namespace Compass\Attributes;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
readonly class Stylesheet
{
    public function __construct(private string $href, private string $media = 'all')
    {
    }

    public static function __set_state(array $data): object
    {
        return new Stylesheet(
            href: $data['href'],
            media: $data['media']
        );
    }

    public function getHref(): string
    {
        return $this->href;
    }

    public function getMedia(): string
    {
        return $this->media;
    }
}