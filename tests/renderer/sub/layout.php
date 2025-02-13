<?php

declare(strict_types=1);

use Mosaic\Renderer;
use Compass\Route;

return function (Renderer $r, $children) {
    yield $r->fragment('<sub>');
    yield $children;
    yield $r->fragment('</sub>');
};