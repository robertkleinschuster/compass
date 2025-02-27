<?php

declare(strict_types=1);

namespace Compass\Templates;

use Compass\Attributes\Header;
use Compass\Exception\InvalidPageRouteException;
use Compass\Exception\InvalidPartialException;
use Compass\Layout;
use Compass\PageRoute;
use Compass\Route;
use Error;
use Mosaic\Exception\RenderException;
use Mosaic\Renderable;
use Mosaic\Renderer;
use ReflectionException;
use Throwable;

readonly class Page implements Renderable
{
    public const string PARTIAL_PARAM = '_partial';

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
            throw new InvalidPageRouteException(sprintf('Route with path `%s` has no page info.', $this->route->getPath()));
        }
    }

    /**
     * @return Header[]
     */
    public function getHeaders(): array
    {
        return $this->route->getPage()->getHeaders();
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
    public function renderLayout(Renderer $renderer, PageRoute $page, mixed $children, array $args, ?string $partial = null): mixed
    {
        $partials = $page->getPartials();

        foreach ($page->getLayoutFiles() as $level => $file) {
            $children = $renderer->render(require $file, $renderer->args([
                ...$args,
                'children' => $children
            ]));

            if ($page->getReactive()) {
                $children = new Layer($children, $partials[$level]);
            }

            if ($partials[$level] === $partial) {
                break;
            }
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

            $page = $this->route->getPage();
            $meta = $page->getMeta();
            $view = require $page->getFile();

            if ($partial) {
                if ($partial !== Layer::CONTENT_ONLY_PARTIAL) {
                    if (!str_starts_with($this->route->getPath(), $partial)) {
                        throw new InvalidPartialException(sprintf('Invalid partial `%s` for route `%s`', $partial, $this->route->getPath()));
                    }

                    $view = $this->renderLayout($renderer, $page, $view, $args, $partial);
                }

                $view = new Partial(
                    children: $view,
                    title: $meta?->getTitle() ?? '',
                    scripts: $page->getScripts(),
                    styles: $page->getStyles()
                );

            } else {
                if ($page->getLazy()) {
                    $view = new Layer($page->getLazy()->getLoading() ?? '', Layer::CONTENT_ONLY_PARTIAL, true);
                }

                $view = $this->renderLayout($renderer, $page, $view, $args);

                $view = new Document(
                    children: $view,
                    lang: $meta?->getLang() ?? 'en',
                    title: $meta?->getTitle() ?? '',
                    description: $meta?->getDescription() ?? '',
                    styles: $page->getStyles(),
                    scripts: $page->getScripts()
                );
            }

            yield $renderer->render($view, $renderer->args($args));
        } catch (Error $error) {
            if ($this->route->getCache()) {
                unlink($this->route->getCache());
            }
            throw $error;
        }
    }
}