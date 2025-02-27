<?php

declare(strict_types=1);

use Compass\Attributes\Script;
use Compass\Attributes\Stylesheet;

return #[Script(__DIR__ . '/page.js')] #[Stylesheet(__DIR__ . '/page.css')] fn() => '<h1>Startpage</h1>';
