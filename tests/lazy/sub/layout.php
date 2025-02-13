<?php

declare(strict_types=1);

use Compass\Route;
use Mosaic\Renderer;

return function (Renderer $r, $children, Route $route) {
    yield $r->fragment('<sub>');
    yield $children;
    yield $r->fragment('</sub>');
};