<?php

declare(strict_types=1);

use Compass\RouteCollector;
use Mosaic\Renderer;
use Compass\Page;

ini_set('display_errors', 1);

require "vendor/autoload.php";

$routeCollector = new RouteCollector(__DIR__ . '/app');
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

foreach ($routeCollector->getRoutes() as $route) {
    if ($route->getPageScriptPath() === $path) {
        header('Content-Type: application/javascript');
        require $route->getPageScriptFile();
        break;
    }

    if ($route->getPageStylesheetPath() === $path) {
        header('Content-Type: text/css');
        require $route->getPageStylesheetFile();
        break;
    }

    if ($route->getLayoutScriptPath() === $path) {
        header('Content-Type: application/javascript');
        require $route->getLayoutScriptFile();
        break;
    }

    if ($route->getLayoutStylesheetPath() === $path) {
        header('Content-Type: text/css');
        require $route->getLayoutStylesheetFile();
        break;
    }

    if ($route->getPath() === $path) {
        if ($route->getPageFile() !== null) {
            $page = new Page($route, $_SERVER['REQUEST_URI'] . '?' . http_build_query($_GET), [], $_GET);
            foreach ($page->getHeaders() as $header) {
                header(sprintf('%s: %s', $header->getName(), $header->getValue()));
            }
            $renderer = new Renderer();
            echo $renderer->render($page);
        }
        break;
    }
}
