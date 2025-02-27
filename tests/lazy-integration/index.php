<?php

declare(strict_types=1);

use Compass\RouteCollector;
use Compass\Templates\Page;
use Mosaic\Renderer;

ini_set('display_errors', 1);

require "vendor/autoload.php";

$routeCollector = new RouteCollector([__DIR__ . '/app']);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

foreach ($routeCollector->getRoutes() as $route) {
    if ($route->getPage()) {
        foreach ($route->getPage()->getScripts() as $script) {
            if ($script->getSrc() === $path) {
                header('Content-Type: application/javascript');
                require $script->getFile();
                break 2;
            }
        }


        foreach ($route->getPage()->getStyles() as $style) {
            if ($style->getHref() === $path) {
                header('Content-Type: text/css');
                require $style->getFile();
                break 2;
            }
        }
    }

    if ($route->getPath() === $path) {
        if ($route->getPage() !== null) {
            $page = new Page($route, $_SERVER['REQUEST_URI'] . '?' . http_build_query($_GET), [], $_GET);
            foreach ($route->getPage()->getHeaders() as $header) {
                header(sprintf('%s: %s', $header->getName(), $header->getValue()));
            }
            $renderer = new Renderer();
            echo $renderer->render($page);
        }
        break;
    }
}
