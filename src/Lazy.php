<?php

declare(strict_types=1);

namespace Compass;

use Attribute;
use Mosaic\Renderer;

#[Attribute(Attribute::TARGET_FUNCTION)]
class Lazy extends Boundary
{
    public function __construct(private mixed $loading = null)
    {
    }

    public function render(Renderer $renderer, mixed $children, mixed $data)
    {
        if (isset($data['partial'])) {
            return $children;
        }

        return parent::render($renderer, $this->loading ?? '', $data);
    }

    protected function buildAttributes(mixed $data): array
    {
        $attributes = parent::buildAttributes($data);
        $attributes[] = 'fetch-on-connected';
        return $attributes;
    }
}