<?php

declare(strict_types=1);

use Compass\Lazy;
use Compass\Route;

return #[Lazy] function (array $params, Route $route) {
    yield 'page ' . $params['id'] . ' ' . $route;
};