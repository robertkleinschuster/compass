<?php

declare(strict_types=1);

return function ($children) {
    yield '<h1>';
    yield $children;
    yield '</h1>';
};