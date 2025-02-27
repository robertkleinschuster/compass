<?php

namespace Compass\Templates;

use Compass\Attributes\Script;
use Compass\Attributes\Stylesheet;
use Mosaic\Fragment;
use Mosaic\Renderable;
use Mosaic\Renderer;

class Partial implements Renderable
{
    /**
     * @param mixed $children
     * @param string $title
     * @param Script[] $scripts
     * @param Stylesheet[] $styles
     */
    public function __construct(
        private mixed  $children,
        private string $title,
        private array $scripts,
        private array $styles
    )
    {
    }

    public function render(Renderer $renderer, mixed $data)
    {
        $assets = json_encode([
            'scripts' => $this->scripts,
            'styles' => $this->styles
        ]);
        $title = htmlentities($this->title);
        yield new Fragment(<<<HTML
<template data-title="$title">{$renderer->render($this->children, $data)}</template>
<script lang="application/json">$assets</script>
HTML
);
    }
}