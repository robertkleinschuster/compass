<?php

declare(strict_types=1);

namespace Compass;

use Attribute;

#[Attribute(Attribute::TARGET_FUNCTION)]
class Reactive extends Boundary
{
}