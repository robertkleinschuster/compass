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
    if ($route->getPath() === $path) {
        $page = new Page($route, $_SERVER['REQUEST_URI'] . '?' . http_build_query($_GET), [], $_GET);
        foreach ($page->getHeaders() as $header) {
            header(sprintf('%s: %s', $header->getName(), $header->getValue()));
        }
        $renderer = new Renderer();
        echo $renderer->render($page);
        break;
    }
}
