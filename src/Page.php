<?php

declare(strict_types=1);

namespace Compass;

use Compass\Attributes\Header;
use Compass\Attributes\Lazy;
use Compass\Attributes\PageMeta;
use Compass\Attributes\PageScript;
use Compass\Attributes\PageStyle;
use Compass\Attributes\Reactive;
use Compass\Attributes\Resource;
use Compass\Exception\InvalidPageRouteException;
use Compass\Exception\InvalidPartialException;
use Compass\Templates\Boundary;
use Compass\Templates\Document;
use Compass\Templates\Partial;
use Error;
use Mosaic\Exception\RenderException;
use Mosaic\Helper\Arguments;
use Mosaic\Renderable;
use Mosaic\Renderer;
use ReflectionException;
use Throwable;

readonly class Page implements Renderable
{
    public const PARTIAL_PARAM = '_partial';
    private mixed $page;
    /** @var Header[] */
    private array $headers;
    private ?PageMeta $meta;
    /** @var PageStyle[] */
    private array $styles;
    /** @var PageScript[] */
    private array $scripts;
    private ?Lazy $lazy;
    private ?Resource $resource;
    private ?Reactive $reactive;

    /**
     * @param Route $route
     * @param string $uri
     * @param array<string, string> $params
     * @param array<string, mixed> $queryParams
     * @throws InvalidPageRouteException|ReflectionException
     */
    public function __construct(private Route $route, private string $uri, private array $params, private array $queryParams)
    {
        if ($this->route->getPage() === null) {
            throw new InvalidPageRouteException(sprintf('Route with path `%s` has no page.', $this->route->getPath()));
        }
        if ($this->route->getPageInfo() === null) {
            throw new InvalidPageRouteException(sprintf('Route with path `%s` has no page info.', $this->route->getPath()));
        }
        $this->page = require $this->route->getPage();
        $this->headers = $this->route->getPageInfo()->getHeaders();
        $this->meta = $this->route->getPageInfo()->getMeta();
        $this->styles = $this->route->getPageInfo()->getStyles();
        $scripts = $this->route->getPageInfo()->getScripts();
        $scripts[] = new PageScript(Boundary::SCRIPT_PATH);
        $this->scripts = $scripts;
        $this->lazy = $this->route->getPageInfo()->getLazy();
        $this->resource = $this->route->getPageInfo()->getResource();
        $this->reactive = $this->route->getPageInfo()->getReactive();
    }

    /**
     * @return Header[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
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
        if ($route->hasLayout()) {
            $layout = require $route->getLayout();
            $children = $renderer->render($layout, $renderer->args([
                ...$args,
                'children' => $renderer->render($children, $renderer->args($args))
            ]));
        }

        if ($this->reactive) {
            $children = new Boundary($children, $route->getPath());
        }

        if ($partial === $route->getPath()) {
            return $children;
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

            if ($this->resource) {
                $view = $this->page;
            } else {
                if ($partial) {

                    if ($partial === Boundary::CONTENT_ONLY_PARTIAL) {
                        $view = $this->page;
                    } else {
                        if (!str_starts_with($this->route->getPath(), $partial)) {
                            throw new InvalidPartialException(sprintf('Invalid partial `%s` for route `%s`', $partial, $this->route->getPath()));
                        }
                        $view = $this->renderLayout($renderer, $this->route, $this->page, (array)$args, $partial);
                    }

                    $view = new Partial(
                        children: $view,
                        title: $this->meta?->getTitle() ?? '',
                        scripts: $this->scripts,
                        styles: $this->styles
                    );

                } else {

                    if ($this->lazy) {
                        $page = new Boundary($this->lazy->getLoading() ?? '', Boundary::CONTENT_ONLY_PARTIAL, true);
                    } else {
                        $page = $this->page;
                    }

                    $view = $this->renderLayout($renderer, $this->route, $page, (array)$args, $partial);

                    $view = new Document(
                        children: $view,
                        lang: $this->meta?->getLang() ?? 'en',
                        title: $this->meta?->getTitle() ?? '',
                        description: $this->meta?->getDescription() ?? '',
                        styles: $this->styles,
                        scripts: $this->scripts
                    );
                }
            }

            yield $renderer->render($view, $args);
        } catch (Error $error) {
            if ($this->route->getCache()) {
                unlink($this->route->getCache());
            }
            throw $error;
        }
    }
}