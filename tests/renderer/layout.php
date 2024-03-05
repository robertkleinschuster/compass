<?php

declare(strict_types=1);

use Mosaic\Renderer;

return function (Renderer $r, $children) {
    yield $r->fragment('<root>');
    yield $children;
    yield $r->fragment('</root>');
};