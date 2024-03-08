<?php

declare(strict_types=1);

namespace Compass;

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
                $scanner = new DirectoryScanner(self::PAGE_FILENAME, self::LAYOUT_FILENAME, self::ACTION_FILENAME);
                $this->routes = $scanner->scan($this->directory);
                $this->cache->save($this->routes);
            }
        } else {
            $scanner = new DirectoryScanner(self::PAGE_FILENAME, self::LAYOUT_FILENAME, self::ACTION_FILENAME);
            $this->routes = $scanner->scan($this->directory);
        }
    }

    public function buildCache(): void
    {
        if ($this->cache) {
            $scanner = new DirectoryScanner(self::PAGE_FILENAME, self::LAYOUT_FILENAME, self::ACTION_FILENAME);
            $this->cache->save($scanner->scan($this->directory));
        }
    }

    /**
     * @deprecated
     * @see self::buildCache()
     * @return void
     */
    public function clearCache(): void
    {
        $this->buildCache();
    }

    /**
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}