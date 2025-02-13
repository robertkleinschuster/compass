<?php

declare(strict_types=1);

use Mosaic\Renderer;

return fn(Renderer $renderer, $children, $route, $request) => $renderer->fragment(<<<HTML
<h1>Hello from imporint child 2 layout!</h1>
{$renderer->render($children, route: $route, request: $request)}
HTML
);