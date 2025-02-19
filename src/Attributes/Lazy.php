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

    /**
     * @param array<string, mixed> $data
     * @return Lazy
     */
    public static function __set_state(array $data): Lazy
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