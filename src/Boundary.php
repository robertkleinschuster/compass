<?php

declare(strict_types=1);

namespace Compass;

use Mosaic\Renderable;
use Mosaic\Renderer;

class Boundary implements Renderable
{
    public function __construct(
        private string $uri,
        private string $route,
        private mixed  $children = null,
        private bool   $fetchOnConnected = false
    )
    {
    }

    public function render(Renderer $renderer, mixed $data = null): iterable
    {
        if (isset($data['children'])) {
            $partial = $this->route;
        } else {
            $partial = '.';
        }

        $attributes = [];
        $attributes[] = sprintf('uri="%s"', $this->uri);
        $attributes[] = sprintf('route="%s"', $this->route);
        $attributes[] = sprintf('partial="%s"', $partial);

        if ($this->fetchOnConnected) {
            $attributes[] = 'fetch-on-connected';
        }

        yield $renderer->fragment(sprintf('<route-boundary %s>', implode(' ', $attributes)));

        if (isset($this->children)) {
            yield $this->children;
        }

        yield $renderer->fragment('</route-boundary>');
    }
}