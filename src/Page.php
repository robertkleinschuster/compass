<?php

declare(strict_types=1);

namespace Compass;

use Error;
use Mosaic\Exception\RenderException;
use Mosaic\Helper\Arguments;
use Mosaic\Renderable;
use Mosaic\Renderer;
use Throwable;

readonly class Page implements Renderable
{
    public const PARTIAL_PARAM = '_partial';

    /**
     * @param Route $route
     * @param string $uri
     * @param array<string, string> $params
     * @param array<string, mixed> $queryParams
     * @param bool $addScripts
     */
    public function __construct(private Route $route, private string $uri, private array $params, private array $queryParams, private bool $addScripts = true)
    {
    }

    /**
     * @param Renderer $renderer
     * @param Route $route
     * @param mixed $children
     * @param array<string, mixed> $args
     * @param string|null $partial
     * @return mixed
     * @throws RenderException
     * @throws Throwable
     */
    public function renderLayout(Renderer $renderer, Route $route, mixed $children, array $args, ?string $partial): mixed
    {
        if (isset($partial) && ($partial === '.' || !str_starts_with($route->getName(), $partial))) {
            return $children;
        }

        if ($route->hasLayout()) {
            $layout = require $route->getLayout();
            $children = $renderer->render($layout, $renderer->args([
                ...$args,
                'children' => $renderer->render($children, $renderer->args($args)),
                'route' => $route
            ]));
        }

        $parent = $route->getParent();
        if ($parent) {
            return $this->renderLayout($renderer, $parent, $children, $args, $partial);
        }

        return $children;
    }

    /**
     * @param Renderer $renderer
     * @param $data
     * @return iterable<int, mixed>
     * @throws RenderException
     * @throws Throwable
     */
    public function render(Renderer $renderer, $data): iterable
    {
        try {
            $partial = $this->queryParams[self::PARTIAL_PARAM] ?? null;

            $args = new Arguments($data ?? []);
            $args['route'] = $this->route;
            $args['partial'] = $partial;
            $args['uri'] = $this->uri;
            $args['params'] = $this->params;
            $args['queryParams'] = $this->queryParams;

            $page = require $this->route->getPage();
            $view = $this->renderLayout($renderer, $this->route, $page, (array)$args, $partial);
            yield $renderer->render($view, $args);

            if ($this->addScripts && !$partial) {
                yield require 'route-boundary.php';
            }
        } catch (Error $error) {
            if ($this->route->getCache()) {
                unlink($this->route->getCache());
            }
            throw $error;
        }
    }
}