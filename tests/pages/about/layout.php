<?php

declare(strict_types=1);

use Compass\Attributes\Script;

return #[Script(__DIR__ . '/layout.js')] function ($children) {
    yield '<h1>';
    yield $children;
    yield '</h1>';
};