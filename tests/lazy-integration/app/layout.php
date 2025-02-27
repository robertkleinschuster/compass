<?php

declare(strict_types=1);

use Compass\Attributes\Stylesheet;
use Mosaic\Renderer;

return #[Stylesheet(__DIR__ . '/layout.css')] fn(Renderer $renderer, $children, $route, $request) => $renderer->fragment(<<<HTML
    <h1>Hello lazy world!</h1>
  {$renderer->render($children, route: $route, request: $request)}
HTML
);