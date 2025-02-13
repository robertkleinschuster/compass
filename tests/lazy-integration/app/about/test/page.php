<?php

declare(strict_types=1);

use Compass\Attributes\Lazy;
use Compass\Attributes\PageMeta;
use Compass\Attributes\PageScript;
use Compass\Attributes\PageStyle;
use Compass\Attributes\Reactive;
use Mosaic\Fragment;

return
    #[Lazy]
    #[Reactive]
    #[PageMeta('de', 'About', '')]
    #[PageScript('/test-script')]
    #[PageStyle('/test-style')]
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