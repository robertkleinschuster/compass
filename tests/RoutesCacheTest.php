<?php

declare(strict_types=1);

namespace CompassTest;

use Compass\AttributesFactory;
use Compass\Route;
use Compass\RoutesCache;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RoutesCacheTest extends TestCase
{
    #[Test]
    public function shouldReturnNullWhenCacheNotSaved()
    {
        $cache = new RoutesCache(__DIR__ . '/routes-cache.php');
        $cache->clear();
        $this->assertNull($cache->load());
    }

    #[Test]
    public function shouldSaveAndLoadRoutes()
    {
        $pageInfo = (new AttributesFactory())->create(require __DIR__ . '/pages/page.php', null, null);

        $route = new Route( path: '/', pageAttributes: $pageInfo);
        $cache = new RoutesCache(__DIR__ . '/routes-cache.php');
        $cache->save([$route]);
        $routes = $cache->load();
        $this->assertCount(1, $routes);
        $this->assertEquals('/', $routes[0]->getPath());
    }
}
