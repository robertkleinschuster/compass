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

    public static function __set_state(array $data): object
    {
        return new Lazy(
            loading: $data['loading']
        );
    }

    public function getLoading(): mixed
    {
        return $this->loading;
    }
}