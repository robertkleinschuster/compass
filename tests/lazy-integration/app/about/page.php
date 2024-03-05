<?php

declare(strict_types=1);

use Mosaic\Fragment;
use Compass\Lazy;
use Compass\Reactive;

return #[Reactive] #[Lazy] function () {
    yield new Fragment('<h2>about!</h2>');
    yield new Fragment('<a href="/1/2/3/4">link</a>');
};