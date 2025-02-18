<?php

declare(strict_types=1);

use Compass\Attributes\MetaInfo;
use Mosaic\Fragment;

return #[MetaInfo('de', 'Hello world!', '')] #[\Compass\Attributes\Reactive] function () {
    yield new Fragment('<a href="/">root</a>');
    yield new Fragment('<a href="/about">about</a>');
    yield new Fragment('<a href="/about/test">about test</a>');
    yield new Fragment('<a href="/about/test2">about test 2</a>');
    yield new Fragment('<a href="/imprint">imprint</a>');
    yield new Fragment('<a href="/imprint/test">imprint test</a>');
    yield new Fragment('<a href="/imprint/test2">imprint test 2</a>');
};