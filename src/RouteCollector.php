<?php

declare(strict_types=1);

namespace Compass;

use Compass\Templates\Layer;
use ReflectionException;

class RouteCollector
{
    public const string PAGE_FILENAME = 'page.php';
    public const string LAYOUT_FILENAME = 'layout.php';
    public const string ACTION_FILENAME = 'action.php';

    /**
     * @var Route[]
     */
    private array $routes;

    /**
     * @param string[] $directories
     * @param RoutesCache|null $cache
     */
    public function __construct(private readonly array $directories, private readonly ?RoutesCache $cache = null)
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

    /**
     * @return Route[]
     * @throws ReflectionException
     */
    private function findRoutes(): array
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME
        );
        return $scanner->scan([
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'routes',
            ...$this->directories
        ]);
    }

    /**
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}