<?php

declare(strict_types=1);

namespace Compass\Templates;

use Mosaic\Renderable;
use Mosaic\Renderer;

readonly class Layer implements Renderable
{
    public const string CONTENT_ONLY_PARTIAL = '.';

    public function __construct(
        private mixed $children,
        private string $partial,
        private bool $fetchOnConnect = false)
    {
    }

    public function render(Renderer $renderer, mixed $data)
    {
        yield $renderer->fragment(sprintf(
            '<route-layer %s>',
            implode(' ', $this->buildAttributes())
        ));
        yield $this->children;
        yield $renderer->fragment('</route-layer>');
    }

    /**
     * @return string[]
     */
    protected function buildAttributes(): array
    {
        $attributes = [];
        $attributes[] = sprintf('partial="%s"', $this->partial);

        if ($this->fetchOnConnect) {
            $attributes[] = 'fetch-on-connected';
        }

        return $attributes;
    }
}