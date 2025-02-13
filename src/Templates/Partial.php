<?php

namespace Compass\Templates;

use Compass\Attributes\PageScript;
use Compass\Attributes\PageStyle;
use Mosaic\Fragment;
use Mosaic\Renderable;
use Mosaic\Renderer;

class Partial implements Renderable
{
    /**
     * @param mixed $children
     * @param string $title
     * @param PageScript[] $scripts
     * @param PageStyle[] $styles
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
        $assets = [];
        foreach ($this->scripts as $script) {
            $assets['scripts'][] = $script->getSrc();
        }
        foreach ($this->styles as $style) {
            $assets['styles'][] = $style->getHref();
        }
        $assets = json_encode($assets);
        $title = htmlentities($this->title);
        yield new Fragment(<<<HTML
<template data-title="$title">{$renderer->render($this->children, $data)}</template>
<script lang="application/json">$assets</script>
HTML
);
    }
}