<?php

declare(strict_types=1);

namespace CompassTest;

use Compass\PageInfoFactory;
use Compass\Route;
use Compass\RoutesCache;
use PHPUnit\Framework\TestCase;

class RoutesCacheTest extends TestCase
{
    public function testShouldReturnNullWhenCacheNotSaved()
    {
        $cache = new RoutesCache(__DIR__ . '/routes-cache.php');
        $cache->clear();
        $this->assertNull($cache->load());
    }

    public function testShouldSaveAndLoadRoutes()
    {
        $pageInfo = (new PageInfoFactory())->create(require __DIR__ . '/pages/page.php', null, null);

        $route = new Route( '/', null, '', null, null, null, null, null, null, $pageInfo);
        $cache = new RoutesCache(__DIR__ . '/routes-cache.php');
        $cache->save([$route]);
        $routes = $cache->load();
        $this->assertCount(1, $routes);
        $this->assertEquals('/', $routes[0]->getPath());
    }
}
