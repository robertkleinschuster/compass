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
use Mosaic\AttributeHelper;
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
    private AttributeHelper $attributeHelper;

    /**
     * @param Route $route
     * @param string $uri
     * @param array<string, string> $params
     * @param array<string, mixed> $queryParams
     * @throws InvalidPageRouteException
     */
    public function __construct(private Route $route, private string $uri, private array $params, private array $queryParams)
    {
        if ($this->route->getPage() === null) {
            throw new InvalidPageRouteException(sprintf('Route with path `%s` has no page.', $this->route->getPath()));
        }
        $this->attributeHelper = new AttributeHelper();
        $this->page = require $this->route->getPage();
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

        if ($this->isReactive()) {
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
     * @return Header[]
     * @throws ReflectionException
     */
    public function getHeaders(): array
    {
        return $this->attributeHelper->getAttributes($this->page, Header::class);
    }

    /**
     * @return PageMeta|null
     * @throws ReflectionException
     */
    public function getMeta(): ?PageMeta
    {
        $attributes = $this->attributeHelper->getAttributes($this->page, PageMeta::class);
        foreach ($attributes as $attribute) {
            return $attribute;
        }
        return null;
    }

    /**
     * @return PageScript[]
     * @throws ReflectionException
     */
    public function getScripts(): array
    {
        $scripts = $this->attributeHelper->getAttributes($this->page, PageScript::class);
        $scripts[] = new PageScript(Boundary::SCRIPT_PATH);
        if ($this->route->getScript() !== null) {
            $scripts[] = new PageScript($this->route->getPath() . '.js');
        }
        return $scripts;
    }

    /**
     * @return PageStyle[]
     * @throws ReflectionException
     */
    public function getStyles(): array
    {
        $styles = $this->attributeHelper->getAttributes($this->page, PageStyle::class);
        if ($this->route->getStylesheet() !== null) {
            $styles[] = new PageStyle($this->route->getPath() . '.css');
        }
        return $styles;
    }

    public function isResource(): bool
    {
        return !empty($this->attributeHelper->getAttributes($this->page, Resource::class));
    }

    public function isReactive(): bool
    {
        return !empty($this->attributeHelper->getAttributes($this->page, Reactive::class));
    }

    public function isLazy(): bool
    {
        return !empty($this->attributeHelper->getAttributes($this->page, Lazy::class));
    }

    public function getLoading(): mixed
    {
        foreach ($this->attributeHelper->getAttributes($this->page, Lazy::class) as $lazy) {
            return $lazy->getLoading();
        }
        return null;
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

            if ($this->isResource()) {
                $view = $page;
            } else {
                $meta = $this->getMeta();

                if ($partial) {

                    if ($partial === Boundary::CONTENT_ONLY_PARTIAL) {
                        $view = $page;
                    } else {
                        if (!str_starts_with($this->route->getPath(), $partial)) {
                            throw new InvalidPartialException(sprintf('Invalid partial `%s` for route `%s`', $partial, $this->route->getPath()));
                        }
                        $view = $this->renderLayout($renderer, $this->route, $page, (array)$args, $partial);
                    }

                    $view = new Partial(
                        children: $view,
                        title: $meta?->getTitle() ?? '',
                        scripts: $this->getScripts(),
                        styles: $this->getStyles()
                    );

                } else {

                    if ($this->isLazy()) {
                        $page = new Boundary($this->getLoading() ?? '', Boundary::CONTENT_ONLY_PARTIAL, true);
                    }

                    $view = $this->renderLayout($renderer, $this->route, $page, (array)$args, $partial);

                    $view = new Document(
                        children: $view,
                        lang: $meta?->getLang() ?? 'en',
                        title: $meta?->getTitle() ?? '',
                        description: $meta?->getDescription() ?? '',
                        styles: $this->getStyles(),
                        scripts: $this->getScripts()
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