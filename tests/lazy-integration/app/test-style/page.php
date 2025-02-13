<?php

return #[\Compass\Attributes\Resource] #[\Compass\Attributes\Header('content-type', 'text/css')] fn() => <<<CSS
body {
    background: #e3dfd9;
    font-family: sans-serif;
}
route-boundary {
    display: flex;
    flex-direction: column;
}
CSS;
