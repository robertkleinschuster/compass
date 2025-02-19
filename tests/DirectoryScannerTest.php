<?php

declare(strict_types=1);

namespace CompassTest;

use Compass\DirectoryScanner;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DirectoryScannerTest extends TestCase
{
    public const PAGE_FILENAME = 'page.php';
    public const PAGE_SCRIPT_FILENAME = 'page.js';
    public const PAGE_STYLESHEET_FILENAME = 'page.css';
    public const LAYOUT_FILENAME = 'layout.php';
    public const LAYOUT_STYLESHEET_FILENAME = 'layout.css';
    public const LAYOUT_SCRIPT_FILENAME = 'layout.js';
    public const ACTION_FILENAME = 'action.php';

    #[Test]
    public function shouldAllowOverridingByDefiningMultipleRoutes()
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
        $routes = $scanner->scan([__DIR__ . '/pages', __DIR__ . '/pages-overrride']);
        $this->assertCount(5, $routes);
        $settings = $routes[3];
        self::assertStringContainsString(__DIR__ . '/pages-overrride/', $settings->getPageFile());
        $about = $routes[1];
        self::assertStringContainsString( __DIR__ . '/pages-overrride/', $about->getPageFile());
        self::assertStringContainsString( __DIR__ . '/pages/', $about->getPageScriptFile());
        self::assertStringContainsString( __DIR__ . '/pages-overrride/', $about->getPageStylesheetFile());
    }

    #[Test]
    public function shouldFindRoutesWithAPage()
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
        $this->assertEquals(__DIR__ . '/pages/users/[id]/page.php', $userId->getPageFile());
    }

    #[Test]
    public function shouldSetLayoutFlagToEachPage()
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
        $routes = $scanner->scan([__DIR__ . '/pages']);
        $this->assertCount(4, $routes);

        $startpage = $routes[0];
        $about = $routes[1];
        $users = $routes[2];
        $userId = $routes[3];

        $this->assertNotNull($startpage->getLayoutFile());
        $this->assertNotNull($about->getLayoutFile());
        $this->assertNotNull($users->getLayoutFile());
        $this->assertNull($userId->getLayoutFile());
    }

    #[Test]
    public function shouldFindActionForRoutes()
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
        $routes = $scanner->scan([__DIR__ . '/actions']);
        $this->assertCount(2, $routes);

        $startpage = $routes[0];
        $child = $routes[1];

        $this->assertEquals(__DIR__ . '/actions/action.php', $startpage->getActionFile());
        $this->assertNull($child->getActionFile());
    }

    #[Test]
    public function shouldIgnoreTrailingSlashesInDirectory()
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
        $routes = $scanner->scan([__DIR__ . '/pages/']);
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
    public function shouldSetTheParentToEachPage()
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
        $routes = $scanner->scan([__DIR__ . '/pages']);

        $startpage = $routes[0];
        $about = $routes[1];
        $users = $routes[2];
        $userId = $routes[3];

        $this->assertNull($startpage->getParent());
        $this->assertEquals($startpage, $about->getParent());
        $this->assertEquals($startpage, $users->getParent());
        $this->assertEquals($users, $userId->getParent());
        $this->assertEquals($startpage, $userId->getParent()->getParent());
    }

    #[Test]
    public function shouldSetScriptAndStylesheetPath()
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
        $routes = $scanner->scan([__DIR__ . '/pages']);

        $root = $routes[0];
        $about = $routes[1];
        $users = $routes[2];

        self::assertSame('/e935a539.js', $root->getPageScriptPath());
        self::assertSame('/d2ecffa0.css', $root->getPageStylesheetPath());
        self::assertSame(__DIR__ . '/pages/about/page.js', $about->getPageScriptFile());
        self::assertSame('/about/e935a539.js', $about->getPageScriptPath());
        self::assertSame(__DIR__ . '/pages/about/page.css', $about->getPageStylesheetFile());
        self::assertSame('/about/d2ecffa0.css', $about->getPageStylesheetPath());
        self::assertNull($users->getPageScriptFile());
        self::assertNull($users->getPageScriptPath());
    }

    #[Test]
    public function shouldSetTheParentToEachPageWhenStartpageIsMissing()
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
        $routes = $scanner->scan([__DIR__ . '/pages-no-startpage']);

        $users = $routes[0];
        $userId = $routes[1];

        $this->assertNull($users->getParent());
        $this->assertEquals($users, $userId->getParent());
    }

    #[Test]
    public function shouldSetTheParentNullWhenThereIsNone()
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
        $routes = $scanner->scan([__DIR__ . '/pages-no-parent']);

        $userId = $routes[0];

        $this->assertNull($userId->getParent());
    }

    #[Test]
    public function shouldSetTheParentToNextHigherLevelWhenMissingIntermediate()
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
        $routes = $scanner->scan([__DIR__ . '/pages-no-intermediate']);

        $startpage = $routes[0];
        $userId = $routes[1];

        $this->assertEquals($startpage, $userId->getParent());
    }
}
