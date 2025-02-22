<?php

return fn(Mosaic\Renderer $renderer) => $renderer->fragment(<<<HTML
    <h1>Welcome</h1>
HTML
);