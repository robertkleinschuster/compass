<?php

declare(strict_types=1);

use Compass\Attributes\Lazy;
use Compass\Attributes\MetaInfo;
use Compass\Attributes\Script;
use Compass\Attributes\Stylesheet;
use Compass\Attributes\Reactive;
use Mosaic\Fragment;

return
    #[Lazy]
    #[Reactive]
    #[MetaInfo('de', 'About', '')]
    #[Script(__DIR__ . '/script.js')]
    #[Stylesheet(__DIR__ . '/styles.css')]
    function () {
        yield new Fragment('<h2>about! 2</h2>');
        yield new Fragment('<a href="/">root</a>');
        yield new Fragment('<a href="/about">about</a>');
        yield new Fragment('<a href="/about/test">about test</a>');
        yield new Fragment('<a href="/about/test2">about test 2</a>');
        yield new Fragment('<a href="/imprint">imprint</a>');
        yield new Fragment('<a href="/imprint/test">imprint test</a>');
        yield new Fragment('<a href="/imprint/test2">imprint test 2</a>');

    };