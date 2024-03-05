<?php

declare(strict_types=1);

return function ($children) {
    yield '<h1>Users</h1>';
    yield $children;
};