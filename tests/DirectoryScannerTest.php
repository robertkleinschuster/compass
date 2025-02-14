<?php

declare(strict_types=1);

namespace CompassTest;

use Compass\DirectoryScanner;
use PHPUnit\Framework\TestCase;

class DirectoryScannerTest extends TestCase
{
    private const PAGE_FILENAME = 'page.php';
    private const LAYOUT_FILENAME = 'layout.php';
    private const ACTION_FILENAME = 'action.php';
    private const SCRIPT_FILENAME = 'script.js';
    private const STYLESHEET_FILENAME = 'styles.css';

    public function testShouldFindRoutesWithAPage()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME,
            stylesheetFilename: self::STYLESHEET_FILENAME,
            scriptFilename: self::SCRIPT_FILENAME
        );
        $routes = $scanner->scan(__DIR__ . '/pages');
        $this->assertCount(4, $routes);

        $startpage = $routes[0];
        $about = $routes[1];
        $users = $routes[2];
        $userId = $routes[3];

        $this->assertEquals('/', $startpage->getPath());
        $this->assertEquals('/about', $about->getPath());
        $this->assertEquals('/users', $users->getPath());
        $this->assertEquals('/users/[id]', $userId->getPath());
        $this->assertEquals(__DIR__ . '/pages/users/[id]/page.php', $userId->getPage());
    }

    public function testShouldSetLayoutFlagToEachPage()
    {

        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME,
            stylesheetFilename: self::STYLESHEET_FILENAME,
            scriptFilename: self::SCRIPT_FILENAME
        );
        $routes = $scanner->scan(__DIR__ . '/pages');
        $this->assertCount(4, $routes);

        $startpage = $routes[0];
        $about = $routes[1];
        $users = $routes[2];
        $userId = $routes[3];

        $this->assertTrue($startpage->hasLayout());
        $this->assertTrue($about->hasLayout());
        $this->assertTrue($users->hasLayout());
        $this->assertFalse($userId->hasLayout());
    }

    public function testShouldFindActionForRoutes()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME,
            stylesheetFilename: self::STYLESHEET_FILENAME,
            scriptFilename: self::SCRIPT_FILENAME
        );
        $routes = $scanner->scan(__DIR__ . '/actions');
        $this->assertCount(2, $routes);

        $startpage = $routes[0];
        $child = $routes[1];

        $this->assertEquals(__DIR__ . '/actions/action.php', $startpage->getAction());
        $this->assertNull($child->getAction());
    }

    public function testShouldIgnoreTrailingSlashesInDirectory()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME,
            stylesheetFilename: self::STYLESHEET_FILENAME,
            scriptFilename: self::SCRIPT_FILENAME
        );
        $routes = $scanner->scan(__DIR__ . '/pages/');
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

    public function testShouldSetTheParentToEachPage()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME,
            stylesheetFilename: self::STYLESHEET_FILENAME,
            scriptFilename: self::SCRIPT_FILENAME
        );
        $routes = $scanner->scan(__DIR__ . '/pages');

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

    public function testShouldSetScriptAndStylesheetPath()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME,
            stylesheetFilename: self::STYLESHEET_FILENAME,
            scriptFilename: self::SCRIPT_FILENAME
        );
        $routes = $scanner->scan(__DIR__ . '/pages');

        $about = $routes[1];

        self::assertSame(__DIR__ . '/pages/about/script.js', $about->getScript());
        self::assertSame(__DIR__ . '/pages/about/styles.css', $about->getStylesheet());
    }

    public function testShouldSetTheParentToEachPageWhenStartpageIsMissing()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME,
            stylesheetFilename: self::STYLESHEET_FILENAME,
            scriptFilename: self::SCRIPT_FILENAME
        );
        $routes = $scanner->scan(__DIR__ . '/pages-no-startpage');

        $users = $routes[0];
        $userId = $routes[1];

        $this->assertNull($users->getParent());
        $this->assertEquals($users, $userId->getParent());
    }

    public function testShouldSetTheParentNullWhenThereIsNone()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME,
            stylesheetFilename: self::STYLESHEET_FILENAME,
            scriptFilename: self::SCRIPT_FILENAME
        );
        $routes = $scanner->scan(__DIR__ . '/pages-no-parent');

        $userId = $routes[0];

        $this->assertNull($userId->getParent());
    }

    public function testShouldSetTheParentToNextHigherLevelWhenMissingIntermediate()
    {
        $scanner = new DirectoryScanner(
            pageFilename: self::PAGE_FILENAME,
            layoutFilename: self::LAYOUT_FILENAME,
            actionFilename: self::ACTION_FILENAME,
            stylesheetFilename: self::STYLESHEET_FILENAME,
            scriptFilename: self::SCRIPT_FILENAME
        );
        $routes = $scanner->scan(__DIR__ . '/pages-no-intermediate');

        $startpage = $routes[0];
        $userId = $routes[1];

        $this->assertEquals($startpage, $userId->getParent());
    }
}
