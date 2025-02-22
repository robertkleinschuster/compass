<?php

declare(strict_types=1);

namespace Compass;

use Compass\Attributes\Header;
use Compass\Attributes\Lazy;
use Compass\Attributes\MetaInfo;
use Compass\Attributes\Script;
use Compass\Attributes\Stylesheet;
use Compass\Attributes\Reactive;
use Compass\Attributes\Resource;
use Compass\Exception\InvalidPageRouteException;
use Compass\Exception\InvalidPartialException;
use Compass\Templates\Layer;
use Compass\Templates\Document;
use Compass\Templates\Partial;
use Error;
use Mosaic\Exception\RenderException;
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
    private ?MetaInfo $meta;
    /** @var Stylesheet[] */
    private array $styles;
    /** @var Script[] */
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
        if ($this->route->getPageFile() === null) {
            throw new InvalidPageRouteException(sprintf('Route with path `%s` has no page.', $this->route->getPath()));
        }
        if ($this->route->getPageAttributes() === null) {
            throw new InvalidPageRouteException(sprintf('Route with path `%s` has no page info.', $this->route->getPath()));
        }
        $this->page = require $this->route->getPageFile();
        $this->headers = $this->route->getPageAttributes()->getHeaders();
        $this->meta = $this->route->getPageAttributes()->getMeta();
        $styles = [new Stylesheet('/.reset.css')];
        $scripts = [new Script(Layer::SCRIPT_PATH)];
        foreach ($this->route->getLayoutStylesheets() as $stylesheet) {
            $styles[] = new Stylesheet($stylesheet);
        }
        foreach ($this->route->getLayoutScripts() as $script) {
            $scripts[] = new Script($script);
        }
        $layoutAttributes = $this->route->getLayoutAttributes();
        if ($layoutAttributes !== null) {
            foreach ($layoutAttributes->getStyles() as $style) {
                $styles[] = $style;
            }
            foreach ($layoutAttributes->getScripts() as $script) {
                $scripts[] = $script;
            }
        }
        if ($this->route->getPageStylesheetPath() !== null) {
            $styles[] = new Stylesheet($this->route->getPageStylesheetPath());
        }
        if ($this->route->getPageScriptPath() !== null) {
            $scripts[] = new Script($this->route->getPageScriptPath());
        }
        foreach ($this->route->getPageAttributes()->getStyles() as $style) {
            $styles[] = $style;
        }
        foreach ($this->route->getPageAttributes()->getScripts() as $script) {
            $scripts[] = $script;
        }
        $this->styles = $styles;
        $this->scripts = $scripts;
        $this->lazy = $this->route->getPageAttributes()->getLazy();
        $this->resource = $this->route->getPageAttributes()->getResource();
        $this->reactive = $this->route->getPageAttributes()->getReactive();
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
        if ($route->getLayoutFile() !== null) {
            $layout = require $route->getLayoutFile();
            $children = $renderer->render($layout, $renderer->args([
                ...$args,
                'children' => $renderer->render($children, $renderer->args($args))
            ]));
        }

        if ($this->reactive) {
            $children = new Layer($children, $route->getPath());
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

            $args = (array)$data;
            $args['route'] = $this->route;
            $args['partial'] = $partial;
            $args['uri'] = $this->uri;
            $args['params'] = $this->params;
            $args['queryParams'] = $this->queryParams;

            if ($this->resource) {
                $view = $this->page;
            } else {
                if ($partial) {

                    if ($partial === Layer::CONTENT_ONLY_PARTIAL) {
                        $view = $this->page;
                    } else {
                        if (!str_starts_with($this->route->getPath(), $partial)) {
                            throw new InvalidPartialException(sprintf('Invalid partial `%s` for route `%s`', $partial, $this->route->getPath()));
                        }
                        $view = $this->renderLayout($renderer, $this->route, $this->page, $args, $partial);
                    }

                    $view = new Partial(
                        children: $view,
                        title: $this->meta?->getTitle() ?? '',
                        scripts: $this->scripts,
                        styles: $this->styles
                    );

                } else {

                    if ($this->lazy) {
                        $page = new Layer($this->lazy->getLoading() ?? '', Layer::CONTENT_ONLY_PARTIAL, true);
                    } else {
                        $page = $this->page;
                    }

                    $view = $this->renderLayout($renderer, $this->route, $page, $args, $partial);

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