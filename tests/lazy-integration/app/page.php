<?php

declare(strict_types=1);

use Mosaic\Fragment;
use Compass\Lazy;

return #[Lazy] function () {
    yield new Fragment('<h1>Hello lazy world!</h1>');
};