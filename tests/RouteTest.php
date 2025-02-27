<?php

declare(strict_types=1);

namespace CompassTest;

use Compass\EntrypointFactory;
use Compass\Route;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    #[Test]
    public function shouldRestorableFromVarExport()
    {
        $entrypointFactory = new EntrypointFactory();
        $layout = $entrypointFactory->createLayout(__DIR__ . '/pages/layout.php', '/');

        $page = $entrypointFactory->createPage(__DIR__ . '/pages/page.php', $layout);

        $startpage = new Route('/');
        $about = new Route('/about', page: $page);

        /** @var $startpageRestored Route */
        eval('$startpageRestored = ' . var_export($startpage, true) . ';');
        /** @var $aboutRestored Route */
        eval('$aboutRestored = ' . var_export($about, true) . ';');

        $this->assertEquals('/', $startpageRestored->getPath());
        $this->assertEquals('/about', $aboutRestored->getPath());
        $this->assertEquals($about->getPage(), $aboutRestored->getPage());
        $this->assertEquals($about->getPage()->getLayoutFiles(), $aboutRestored->getPage()->getLayoutFiles());
    }
}
