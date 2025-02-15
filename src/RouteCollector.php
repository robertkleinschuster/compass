<?php

declare(strict_types=1);

namespace Compass;

use Compass\Templates\Boundary;

class RouteCollector
{
    public const PAGE_FILENAME = 'page.php';
    public const LAYOUT_FILENAME = 'layout.php';
    public const ACTION_FILENAME = 'action.php';
    public const SCRIPT_FILENAME = 'script.js';
    public const STYLESHEET_FILENAME = 'styles.css';

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
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME,
            stylesheetFilename: self::STYLESHEET_FILENAME,
            scriptFilename: self::SCRIPT_FILENAME
        );
        $routes = $scanner->scan($this->directory);
        $routes[] = new Route(
            substr(Boundary::SCRIPT_PATH, 0, strlen(Boundary::SCRIPT_PATH) - 3),
            null,
            null,
            null,
            null,
            null,
            __DIR__ . '/Templates/client-router.js'
        );
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