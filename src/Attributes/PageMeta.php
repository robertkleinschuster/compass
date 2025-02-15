<?php

namespace Compass\Attributes;

use Attribute;

#[Attribute]
readonly class PageMeta
{
    public function __construct(
        private string $lang,
        private string $title,
        private string $description
    )
    {
    }

    public static function __set_state(array $data): object
    {
        return new PageMeta(
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