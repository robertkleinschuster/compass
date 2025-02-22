<?php

declare(strict_types=1);

namespace Compass;

use Compass\Templates\Layer;
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
        $routes = $scanner->scan([
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'routes',
            ...$this->directories
        ]);
        $routes[] = new Route(
            pageScriptFile: dirname(__DIR__) . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'runtime.js',
            pageScriptPath: Layer::SCRIPT_PATH
        );
        $routes[] = new Route(
            pageStylesheetFile: dirname(__DIR__) . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'reset.css',
            pageStylesheetPath: '/.reset.css'
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