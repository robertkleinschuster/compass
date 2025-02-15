<?php

declare(strict_types=1);

namespace CompassTest;

use Compass\Action;
use PHPUnit\Framework\TestCase;
use Compass\Route;

class ActionTest extends TestCase
{
    public function testShouldInvokeActionWithParams()
    {
        $route = new Route('', null, null, null, __DIR__ . '/actions/action.php', null, null, null, null, null);
        $handler = new Action($route, ['id' => '1'], [], []);
        $result = $handler->call();
        $this->assertEquals('test 1', $result);
    }
}
