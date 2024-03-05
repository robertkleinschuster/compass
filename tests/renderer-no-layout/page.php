<?php

declare(strict_types=1);

return function (array $params) {
    yield 'page ' . $params['id'];
};