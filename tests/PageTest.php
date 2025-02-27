<?php

declare(strict_types=1);

namespace CompassTest;

use Compass\EntrypointFactory;
use Compass\Route;
use Compass\Templates\Page;
use Mosaic\Renderer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    #[Test]
    public function testRender()
    {
        $entrypointFactory = new EntrypointFactory();
        $layout = $entrypointFactory->createLayout(__DIR__ . '/renderer/layout.php', '/');
        $layout = $entrypointFactory->createNestedLayout(__DIR__ . '/renderer/sub/layout.php', '/sub', $layout);
        $layout = $entrypointFactory->createNestedLayout(__DIR__ . '/renderer/sub/child/layout.php', '/sub/child', $layout);
        $child = new Route(path: '/sub/child', page: $entrypointFactory->createPage(__DIR__ . '/renderer/sub/child/page.php', $layout));

        $renderer = new Renderer();
        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], []));
        $this->assertStringContainsString('<root><sub><child id="1">page 1 /sub/child</child></sub></root>', (string)$result);
    }

    #[Test]
    public function testRenderNoLayout()
    {
        $entrypointFactory = new EntrypointFactory();

        $root = new Route('/', $entrypointFactory->createPage(__DIR__ . '/renderer-no-layout/page.php', null));

        $renderer = new Renderer();
        $result = $renderer->render(new Page($root, '/sub/child', ['id' => '1'], []));
        $this->assertStringContainsString('page 1', (string)$result);
    }

    #[Test]
    public function testRenderPartial()
    {
        $entrypointFactory = new EntrypointFactory();

        $layout = $entrypointFactory->createLayout(__DIR__ . '/renderer/layout.php', '/');
        $layout = $entrypointFactory->createNestedLayout(__DIR__ . '/renderer/sub/layout.php', '/sub', $layout);
        $layout = $entrypointFactory->createNestedLayout(__DIR__ . '/renderer/sub/child/layout.php', '/sub/child', $layout);
        $child = new Route(path: '/sub/child', page: $entrypointFactory->createPage(__DIR__ . '/renderer/sub/child/page.php', $layout));

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

    #[Test]
    public function testRenderLazy()
    {
        $entrypointFactory = new EntrypointFactory();

        $layout = $entrypointFactory->createLayout(__DIR__ . '/lazy/layout.php', '/');
        $layout = $entrypointFactory->createNestedLayout(__DIR__ . '/lazy/sub/layout.php', '/sub', $layout);
        $layout = $entrypointFactory->createNestedLayout(__DIR__ . '/lazy/sub/child/layout.php', '/sub/child', $layout);
        $child = new Route(path: '/sub/child', page: $entrypointFactory->createPage(__DIR__ . '/lazy/sub/child/page.php', $layout));



        $renderer = new Renderer();

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], []));
        $this->assertStringContainsString('<root><sub><child id="1"><route-layer partial="." fetch-on-connected></route-layer></child></sub></root>', (string)$result);

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], ['_partial' => '/sub']));
        $this->assertStringContainsString('<template data-title=""><sub><child id="1">page 1 /sub/child</child></sub></template>', (string)$result);

        $layout = $entrypointFactory->createLayout(__DIR__ . '/lazy/layout.php', '/');
        $layout = $entrypointFactory->createNestedLayout(__DIR__ . '/lazy/sub/child/layout.php', '/sub/child', $layout);
        $child = new Route(path: '/sub/child', page: $entrypointFactory->createPage(__DIR__ . '/lazy/sub/child/page.php', $layout));


        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], []));
        $this->assertStringContainsString('<root><child id="1"><route-layer partial="." fetch-on-connected></route-layer></child></root>', (string)$result);

        $result = $renderer->render(new Page($child, '/sub/child', ['id' => '1'], ['_partial' => '.']));
        $this->assertStringContainsString('<template data-title="">page 1 /sub/child</template>', (string)$result);
    }
}
