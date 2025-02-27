<?php

declare(strict_types=1);

use Compass\Attributes\Script;

return #[Script(__DIR__ . '/layout.js')] function ($children) {
    yield '<body>';
    yield $children;
    yield '</body>';
};