<?php

declare(strict_types=1);

namespace Compass;

use Compass\Attributes\Boundary;

class RouteCollector
{
    public const PAGE_FILENAME = 'page.php';
    public const LAYOUT_FILENAME = 'layout.php';
    public const ACTION_FILENAME = 'action.php';

    /**
     * @var Route[]
     */
    private array $routes;

    public function __construct(private readonly string $directory, private readonly ?RoutesCache $cache = null)
    {
        if ($this->cache) {
            $routes = $this->cache->load();
            if ($routes) {
                $this->routes = $routes;
            } else {
                $this->routes = $this->findRoutes();
                $this->buildCache();
            }
        } else {
            $this->routes = $this->findRoutes();
        }
    }

    public function buildCache(): void
    {
        $this->cache?->save($this->routes);
    }

    private function findRoutes(): array
    {
        $scanner = new DirectoryScanner(self::PAGE_FILENAME, self::LAYOUT_FILENAME, self::ACTION_FILENAME);
        $routes = $scanner->scan($this->directory);
        $routes[] = new Route(Boundary::SCRIPT_PATH, null, __DIR__ . '/Templates/client-router.js.php', null, null);
        return $routes;
    }

    /**
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}