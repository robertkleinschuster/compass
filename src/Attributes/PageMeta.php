<?php

namespace Compass\Attributes;

use Attribute;
use Stringable;

#[Attribute]
readonly class PageMeta
{
    public function __construct(
        private string|Stringable $lang,
        private string|Stringable $title,
        private string|Stringable $description
    )
    {
    }

    public function getLang(): string
    {
        return (string)$this->lang;
    }

    public function getTitle(): string
    {
        return (string)$this->title;
    }

    public function getDescription(): string
    {
        return (string)$this->description;
    }
}