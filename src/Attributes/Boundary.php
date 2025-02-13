<?php

declare(strict_types=1);

namespace Compass\Attributes;

use Attribute;
use Mosaic\RenderableAttribute;
use Mosaic\Renderer;

#[Attribute]
class Boundary implements RenderableAttribute
{
    public const SCRIPT_PATH = '/.client-router.js';

    public function render(Renderer $renderer, mixed $children, mixed $data)
    {
        yield $renderer->fragment(sprintf(
            '<route-boundary %s>',
            implode(' ', $this->buildAttributes($data))
        ));
        yield $children;
        yield $renderer->fragment('</route-boundary>');
    }

    protected function buildAttributes(mixed $data): array
    {
        if (isset($data['children']) && isset($data['route'])) {
            $partial = $data['route'];
        } else {
            $partial = '.';
        }

        $attributes = [];
        if (isset($data['uri'])) {
            $attributes[] = sprintf('uri="%s"', $data['uri']);
        }

        if (isset($data['route'])) {
            $attributes[] = sprintf('route="%s"', $data['route']);
        }

        $attributes[] = sprintf('partial="%s"', $partial);

        return $attributes;
    }
}