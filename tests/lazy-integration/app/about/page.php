<?php

declare(strict_types=1);

use Compass\Attributes\Script;
use Compass\Attributes\Stylesheet;
use Mosaic\Fragment;

return #[Script(__DIR__ . '/page.js')] #[Stylesheet(__DIR__ . '/page.css')] #[\Compass\Attributes\Lazy('Loading...')] #[\Compass\Attributes\Reactive] #[\Compass\Attributes\MetaInfo('de', 'About', '')] function () {
    yield new Fragment('<h2>about!</h2>');
    yield new Fragment('<a href="/">root</a>');
    yield new Fragment('<a href="/about">about</a>');
    yield new Fragment('<a href="/about/test">about test</a>');
    yield new Fragment('<a href="/about/test2">about test 2</a>');
    yield new Fragment('<a href="/imprint">imprint</a>');
    yield new Fragment('<a href="/imprint/test">imprint test</a>');
    yield new Fragment('<a href="/imprint/test2">imprint test 2</a>');
};