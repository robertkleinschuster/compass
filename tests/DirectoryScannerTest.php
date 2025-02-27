<?php

declare(strict_types=1);

namespace CompassTest;

use Compass\DirectoryScanner;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DirectoryScannerTest extends TestCase
{
    public const PAGE_FILENAME = 'page.php';
    public const LAYOUT_FILENAME = 'layout.php';
    public const ACTION_FILENAME = 'action.php';

    #[Test]
    public function shouldFindRoutesWithAPage()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME
        );
        $routes = $scanner->scan([__DIR__ . '/pages']);
        $this->assertCount(4, $routes);

        $startpage = $routes[0];
        $about = $routes[1];
        $users = $routes[2];
        $userId = $routes[3];

        $this->assertEquals('/', $startpage->getPath());
        $this->assertEquals('/about', $about->getPath());
        $this->assertEquals('/users', $users->getPath());
        $this->assertEquals('/users/[id]', $userId->getPath());
    }


    #[Test]
    public function shouldIgnoreTrailingSlashesInDirectory()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME
        );
        $routes = $scanner->scan([__DIR__ . '/pages/']);
        $this->assertCount(4, $routes);
        $this->assertEquals('/', $routes[0]->getPath());
    }

    #[Test]
    public function shouldLoadPageEntrypoint()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME
        );
        $routes = $scanner->scan([__DIR__ . '/pages']);
        $this->assertEquals(__DIR__ . '/pages/page.php', $routes[0]->getPage()->getFile());
    }

    #[Test]
    public function shouldLoadLayoutEntrypoint()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME
        );
        $routes = $scanner->scan([__DIR__ . '/pages']);
        $this->assertEquals(__DIR__ . '/pages/layout.php', $routes[3]->getPage()->getLayoutFiles()[1]);
        $this->assertEquals('/', $routes[3]->getPage()->getPartials()[1]);
    }

    #[Test]
    public function shouldLoadParentLayoutEntrypoint()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME
        );
        $routes = $scanner->scan([__DIR__ . '/pages']);
        $this->assertEquals(__DIR__ . '/pages/users/layout.php', $routes[3]->getPage()->getLayoutFiles()[0]);
        $this->assertEquals('/users', $routes[3]->getPage()->getPartials()[0]);
        $this->assertEquals(__DIR__ . '/pages/layout.php', $routes[3]->getPage()->getLayoutFiles()[1]);
        $this->assertEquals('/', $routes[3]->getPage()->getPartials()[1]);
    }

    #[Test]
    public function shouldAllowNoLayout()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME
        );
        $routes = $scanner->scan([__DIR__ . '/pages-overrride']);
        $this->assertEmpty($routes[0]->getPage()->getLayoutFiles());
    }


    #[Test]
    public function shouldPrependLayoutScriptsAndStylesFromParents()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME
        );
        $routes = $scanner->scan([__DIR__ . '/pages']);
        $this->assertCount(4, $routes);

        $about = $routes[1];

        $this->assertEquals(__DIR__ . '/pages/layout.js', $about->getPage()->getScripts()[1]->getFile());
        $this->assertEquals(__DIR__ . '/pages/about/layout.js', $about->getPage()->getScripts()[2]->getFile());
    }

    #[Test]
    public function shouldPrependRuntimeScriptAndResetStyles()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME
        );
        $routes = $scanner->scan([__DIR__ . '/pages']);
        $this->assertCount(4, $routes);

        $about = $routes[1];

        $this->assertEquals(realpath(__DIR__ . '/../src/runtime.js'), $about->getPage()->getScripts()[0]->getFile());
        $this->assertEquals(realpath(__DIR__ . '/../src/reset.css'), $about->getPage()->getStyles()[0]->getFile());
    }

    #[Test]
    public function shouldAllowOverridingByDefiningMultipleDirectories()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME
        );
        $routes = $scanner->scan([__DIR__ . '/pages', __DIR__ . '/pages-overrride']);
        $this->assertCount(5, $routes);
        $settings = $routes[3];
        self::assertStringContainsString(__DIR__ . '/pages-overrride/', $settings->getPage()->getFile());
        $about = $routes[1];
        self::assertStringContainsString(__DIR__ . '/pages-overrride/', $about->getPage()->getFile());
    }


    #[Test]
    public function shouldFindActionForRoutes()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME
        );
        $routes = $scanner->scan([__DIR__ . '/actions']);
        $this->assertCount(2, $routes);

        $startpage = $routes[0];
        $child = $routes[1];

        $this->assertEquals(__DIR__ . '/actions/action.php', $startpage->getActionFile());
        $this->assertNull($child->getActionFile());
    }

    #[Test]
    public function shouldSetScriptAndStylesheetPath()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME
        );
        $routes = $scanner->scan([__DIR__ . '/pages']);

        $root = $routes[0];
        $about = $routes[1];
        $users = $routes[2];

        self::assertSame('/static/e935a539.js', $root->getPage()->getScripts()[1]->getSrc());
        self::assertSame('/static/d2ecffa0.css', $root->getPage()->getStyles()[1]->getHref());
        self::assertSame(__DIR__ . '/pages/about/page.js', $about->getPage()->getScripts()[3]->getFile());
        self::assertSame('/static/e935a539.js', $about->getPage()->getScripts()[3]->getSrc());
        self::assertSame(__DIR__ . '/pages/about/page.css', $about->getPage()->getStyles()[1]->getFile());
        self::assertSame('/static/d2ecffa0.css', $about->getPage()->getStyles()[1]->getHref());
        self::assertSame(__DIR__ . '/pages/users/page.js', $users->getPage()->getScripts()[2]->getFile());
    }
}
