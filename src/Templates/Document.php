<?php

namespace Compass\Templates;

use Compass\Attributes\PageScript;
use Compass\Attributes\PageStyle;
use Mosaic\Fragment;
use Mosaic\Renderable;
use Mosaic\Renderer;

class Document implements Renderable
{
    /**
     * @param mixed $children
     * @param string $lang
     * @param string $title
     * @param string $description
     * @param PageStyle[] $styles
     * @param PageScript[] $scripts
     */
    public function __construct(
        private mixed $children,
        private string $lang,
        private string $title,
        private string $description,
        private array  $styles = [],
        private array  $scripts = [],
    )
    {
    }


    public function render(Renderer $renderer, mixed $data)
    {
        yield new Fragment(<<<HTML
<!doctype html>
<html lang="$this->lang">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>$this->title</title>
    <meta name="description" content="$this->description">
    {$renderer->foreach($this->styles, fn(PageStyle $style) => new Fragment('<link rel="stylesheet" href="{href}">', href: $style->getHref()))}
    {$renderer->foreach($this->scripts, fn(PageScript $script) => new Fragment('<script src="{src}"></script>', src: $script->getSrc()))}
</head>
<body>
    {$renderer->render($this->children, $data)}
</body>
</html>
HTML
        );
    }
}