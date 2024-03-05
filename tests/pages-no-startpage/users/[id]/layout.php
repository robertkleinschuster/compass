<?php

declare(strict_types=1);

return function ($children) {
    yield '<h2>John Doe</h2>';
    yield $children;
};