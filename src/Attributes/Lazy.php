<?php

declare(strict_types=1);

namespace Compass\Attributes;

use Attribute;
use Mosaic\Renderer;

#[Attribute]
class Lazy
{
    public function __construct(private mixed $loading = null)
    {
    }

    public function getLoading(): mixed
    {
        return $this->loading;
    }
}