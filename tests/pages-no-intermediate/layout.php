<?php

declare(strict_types=1);

return function ($children) {
    yield '<body>';
    yield $children;
    yield '</body>';
};