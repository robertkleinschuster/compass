<?php

declare(strict_types=1);

namespace CompassTest;

use Compass\Action;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Compass\Route;

class ActionTest extends TestCase
{
    #[Test]
    public function shouldInvokeActionWithParams()
    {
        $route = new Route(path: '', actionFile: __DIR__ . '/actions/action.php');
        $handler = new Action($route, ['id' => '1'], [], []);
        $result = $handler->call();
        $this->assertEquals('test 1', $result);
    }
}
