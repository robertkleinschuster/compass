<?php

use Compass\Attributes\Header;
use Compass\Attributes\Resource;
use Mosaic\Fragment;

return #[Resource] #[Header('content-type', 'application/javascript')] fn() => new Fragment(<<<JS
console.log('test')
JS
);
