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
        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], []));
        $this->assertStringContainsString('<root><sub><child id="1">page 1 /sub/child</child></sub></root>', (string)$result);
    }

    public function testRenderNoLayout()
    {
        $root = new Route('', null, __DIR__ . '/renderer-no-layout/page.php', null, null);

        $renderer = new Renderer();
        $result = $renderer->render(new Page($root, '/sub/child', ['id' => '1'], []));
        $this->assertStringContainsString('page 1', (string)$result);
    }

    public function testRenderPartial()
    {
        $root = new Route('/', null, __DIR__ . '/renderer/page.php', __DIR__ . '/renderer/layout.php', null);
        $sub = new Route('/sub', $root, __DIR__ . '/renderer/sub/page.php', __DIR__ . '/renderer/sub/layout.php', null);
        $child = new Route('/sub/child', $sub, __DIR__ . '/renderer/sub/child/page.php', __DIR__ . '/renderer/sub/child/layout.php', null);

        $renderer = new Renderer();

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], ['_partial' => '.']));
        $this->assertStringContainsString('<template data-title="">page 1 /sub/child</template>', (string)$result);

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], ['_partial' => '/sub/child']));
        $this->assertStringContainsString('<template data-title=""><child id="1">page 1 /sub/child</child></template>', (string)$result);

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], ['_partial' => '/sub']));
        $this->assertStringContainsString('<template data-title=""><sub><child id="1">page 1 /sub/child</child></sub></template>', (string)$result);

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], ['_partial' => '/']));
        $this->assertStringContainsString('<template data-title=""><root><sub><child id="1">page 1 /sub/child</child></sub></root></template>', (string)$result);
    }

    public function testRenderLazy()
    {
        $root = new Route('/', null, __DIR__ . '/lazy/page.php', __DIR__ . '/lazy/layout.php', null);
        $sub = new Route('/sub', $root, __DIR__ . '/lazy/sub/page.php', __DIR__ . '/lazy/sub/layout.php', null);
        $child = new Route('/sub/child', $sub, __DIR__ . '/lazy/sub/child/page.php', __DIR__ . '/lazy/sub/child/layout.php', null);

        $renderer = new Renderer();

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], []));
        $this->assertStringContainsString('<root><sub><child id="1"><route-boundary partial="." fetch-on-connected></route-boundary></child></sub></root>', (string)$result);

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], ['_partial' => '/sub']));
        $this->assertStringContainsString('<template data-title=""><sub><child id="1">page 1 /sub/child</child></sub></template>', (string)$result);

        $root = new Route('/', null, __DIR__ . '/lazy/page.php', __DIR__ . '/lazy/layout.php', null);
        $sub = new Route('/sub', $root, __DIR__ . '/lazy/sub/page.php', null, null);
        $child = new Route('/sub/child', $sub, __DIR__ . '/lazy/sub/child/page.php', __DIR__ . '/lazy/sub/child/layout.php', null);

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], []));
        $this->assertStringContainsString('<root><child id="1"><route-boundary partial="." fetch-on-connected></route-boundary></child></root>', (string)$result);

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], ['_partial' => '.']));
        $this->assertStringContainsString('<template data-title="">page 1 /sub/child</template>', (string)$result);
    }
}
