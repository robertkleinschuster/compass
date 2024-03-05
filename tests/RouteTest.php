<?php

declare(strict_types=1);

namespace CompassTest;

use Compass\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testShouldRestorableFromVarExport()
    {
        $startpage = new Route('/', null, '', 'test', null);
        $about = new Route('/about', $startpage, '', null, null);

        /** @var $startpageRestored Route */
        eval('$startpageRestored = ' . var_export($startpage, true) . ';');
        /** @var $aboutRestored Route */
        eval('$aboutRestored = ' . var_export($about, true) . ';');

        $this->assertEquals('/', $startpageRestored->getPath());
        $this->assertTrue($startpageRestored->hasLayout());
        $this->assertEquals('/about', $aboutRestored->getPath());
        $this->assertFalse($aboutRestored->hasLayout());
        $this->assertEquals($startpageRestored, $aboutRestored->getParent());
    }
}
