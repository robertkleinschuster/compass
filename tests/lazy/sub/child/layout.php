<?php

declare(strict_types=1);

use Mosaic\Renderer;

return function (Renderer $r, $children, array $params) {
    yield $r->fragment("<child id=\"$params[id]\">");
    yield $children;
    yield $r->fragment('</child>');
};