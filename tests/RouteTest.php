<?php

declare(strict_types=1);

namespace CompassTest;

use Compass\AttributesFactory;
use Compass\Route;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    #[Test]
    public function shouldRestorableFromVarExport()
    {
        $pageInfo = (new AttributesFactory())->create(require __DIR__ . '/pages/page.php', null, null);

        $startpage = new Route('/', layoutFile: 'layout');
        $about = new Route('/about', $startpage, pageFile: 'page', pageAttributes:  $pageInfo);

        /** @var $startpageRestored Route */
        eval('$startpageRestored = ' . var_export($startpage, true) . ';');
        /** @var $aboutRestored Route */
        eval('$aboutRestored = ' . var_export($about, true) . ';');

        $this->assertEquals('/', $startpageRestored->getPath());
        $this->assertNotNull($startpageRestored->getLayoutFile());
        $this->assertEquals('/about', $aboutRestored->getPath());
        $this->assertNull($aboutRestored->getLayoutFile());
        $this->assertEquals($startpageRestored, $aboutRestored->getParent());
        $this->assertEquals($about->getPageAttributes(), $aboutRestored->getPageAttributes());
    }
}
