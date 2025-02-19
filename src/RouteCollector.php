<?php

declare(strict_types=1);

namespace Compass;

use Compass\Templates\Boundary;
use ReflectionException;

class RouteCollector
{
    public const string PAGE_FILENAME = 'page.php';
    public const string PAGE_SCRIPT_FILENAME = 'page.js';
    public const string PAGE_STYLESHEET_FILENAME = 'page.css';
    public const string LAYOUT_FILENAME = 'layout.php';
    public const string LAYOUT_STYLESHEET_FILENAME = 'layout.css';
    public const string LAYOUT_SCRIPT_FILENAME = 'layout.js';
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
            pageStylesheetFilename: self::PAGE_STYLESHEET_FILENAME,
            pageScriptFilename: self::PAGE_SCRIPT_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            layoutStylesheetFilename: self::LAYOUT_STYLESHEET_FILENAME,
            layoutScriptFilename: self::LAYOUT_SCRIPT_FILENAME,
            actionFilename: self::ACTION_FILENAME
        );
        $routes = $scanner->scan($this->directories);
        $routes[] = new Route(
            path: substr(Boundary::SCRIPT_PATH, 0, strlen(Boundary::SCRIPT_PATH) - 3),
            pageScriptFile: __DIR__ . '/Templates/client-router.js',
            pageScriptPath: Boundary::SCRIPT_PATH
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