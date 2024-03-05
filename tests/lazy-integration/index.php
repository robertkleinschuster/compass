<?php

declare(strict_types=1);

use Mosaic\Renderer;
use Compass\Page;
use Compass\Route;

ini_set('display_errors', 1);

require "../../vendor/autoload.php";

$root = new Route('/', null, __DIR__ . '/app/page.php', __DIR__ . '/app/layout.php', null);
$about = new Route('/1/2/3/4', $root, __DIR__ . '/app/about/page.php', __DIR__ . '/app/about/layout.php', null);

$page = new Page($about, $_SERVER['REQUEST_URI'] . '?' . http_build_query($_GET), [], $_GET);
$renderer = new Renderer();
echo $renderer->render($page);



