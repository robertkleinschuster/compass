<?php

declare(strict_types=1);

use Compass\Route;

return function (array $params, Route $route) {
    yield 'page ' . $params['id'] . ' ' . $route;
};