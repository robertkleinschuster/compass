<?php

declare(strict_types=1);

namespace CompassTest;

use Compass\PageInfoFactory;
use Compass\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testShouldRestorableFromVarExport()
    {
        $pageInfo = (new PageInfoFactory())->create(require __DIR__ . '/pages/page.php', null, null);

        $startpage = new Route('/', null, '', 'test', null, null, null, null);
        $about = new Route('/about', $startpage, '', null, null, null, null, $pageInfo);

        /** @var $startpageRestored Route */
        eval('$startpageRestored = ' . var_export($startpage, true) . ';');
        /** @var $aboutRestored Route */
        eval('$aboutRestored = ' . var_export($about, true) . ';');

        $this->assertEquals('/', $startpageRestored->getPath());
        $this->assertTrue($startpageRestored->hasLayout());
        $this->assertEquals('/about', $aboutRestored->getPath());
        $this->assertFalse($aboutRestored->hasLayout());
        $this->assertEquals($startpageRestored, $aboutRestored->getParent());
        $this->assertEquals($about->getPageInfo(), $aboutRestored->getPageInfo());
    }
}
