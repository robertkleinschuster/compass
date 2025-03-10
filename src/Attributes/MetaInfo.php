<?php

namespace Compass\Attributes;

use Attribute;

#[Attribute]
readonly class MetaInfo
{
    public function __construct(
        private string $lang,
        private string $title,
        private string $description
    )
    {
    }

    /**
     * @param array<string, string> $data
     * @return MetaInfo
     */
    public static function __set_state(array $data): MetaInfo
    {
        return new MetaInfo(
            lang: $data['lang'],
            title: $data['title'],
            description: $data['description']
        );
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}