<?php

declare(strict_types=1);

namespace CompassTest;

use Mosaic\Renderer;
use Compass\Route;
use Compass\Page;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    public function testRender()
    {
        $root = new Route('/', null, __DIR__ . '/renderer/page.php', __DIR__ . '/renderer/layout.php', null);
        $sub = new Route('/sub', $root, __DIR__ . '/renderer/sub/page.php', __DIR__ . '/renderer/sub/layout.php', null);
        $child = new Route('/sub/child', $sub, __DIR__ . '/renderer/sub/child/page.php', __DIR__ . '/renderer/sub/child/layout.php', null);

        $renderer = new Renderer();
        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], [], false));
        $this->assertEquals('<root><sub id="/sub"><child id="1">page 1 /sub/child</child></sub></root>', (string)$result);
    }

    public function testRenderNoLayout()
    {
        $root = new Route('', null, __DIR__ . '/renderer-no-layout/page.php', null, null);

        $renderer = new Renderer();
        $result = $renderer->render(new Page($root, '/sub/child', ['id' => '1'], [], false));
        $this->assertEquals('page 1', $result);
    }

    public function testRenderPartial()
    {
        $root = new Route('/', null, __DIR__ . '/renderer/page.php', __DIR__ . '/renderer/layout.php', null);
        $sub = new Route('/sub', $root, __DIR__ . '/renderer/sub/page.php', __DIR__ . '/renderer/sub/layout.php', null);
        $child = new Route('/sub/child', $sub, __DIR__ . '/renderer/sub/child/page.php', __DIR__ . '/renderer/sub/child/layout.php', null);

        $renderer = new Renderer();

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], ['_partial' => '.']));
        $this->assertEquals('page 1 /sub/child', (string)$result);

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], ['_partial' => '/sub/child']));
        $this->assertEquals('<child id="1">page 1 /sub/child</child>', (string)$result);

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], ['_partial' => '/sub']));
        $this->assertEquals('<sub id="/sub"><child id="1">page 1 /sub/child</child></sub>', (string)$result);

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], ['_partial' => '/']));
        $this->assertEquals('<root><sub id="/sub"><child id="1">page 1 /sub/child</child></sub></root>', (string)$result);
    }

    public function testRenderLazy()
    {
        $root = new Route('/', null, __DIR__ . '/lazy/page.php', __DIR__ . '/lazy/layout.php', null);
        $sub = new Route('/sub', $root, __DIR__ . '/lazy/sub/page.php', __DIR__ . '/lazy/sub/layout.php', null);
        $child = new Route('/sub/child', $sub, __DIR__ . '/lazy/sub/child/page.php', __DIR__ . '/lazy/sub/child/layout.php', null);

        $renderer = new Renderer();

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], [], false));
        $this->assertEquals('<root><route-boundary uri="/sub/child" route="/sub" partial="/sub" fetch-on-connected></route-boundary></root>', (string)$result);

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], ['_partial' => '/sub']));
        $this->assertEquals('<sub id="/sub"><child id="1">page 1 /sub/child</child></sub>', (string)$result);

        $root = new Route('/', null, __DIR__ . '/lazy/page.php', __DIR__ . '/lazy/layout.php', null);
        $sub = new Route('/sub', $root, __DIR__ . '/lazy/sub/page.php', null, null);
        $child = new Route('/sub/child', $sub, __DIR__ . '/lazy/sub/child/page.php', __DIR__ . '/lazy/sub/child/layout.php', null);

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], [], false));
        $this->assertEquals('<root><child id="1"><route-boundary uri="/sub/child" route="/sub/child" partial="." fetch-on-connected></route-boundary></child></root>', (string)$result);

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], ['_partial' => '.']));
        $this->assertEquals('page 1 /sub/child', (string)$result);
    }
}
