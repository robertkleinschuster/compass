<?php

namespace Compass;

use Compass\Attributes\Header;
use Compass\Attributes\Lazy;
use Compass\Attributes\MetaInfo;
use Compass\Attributes\Script;
use Compass\Attributes\Stylesheet;
use Compass\Attributes\Reactive;
use Mosaic\AttributeHelper;
use ReflectionException;

class EntrypointFactory
{
    private AttributeHelper $attributeHelper;


    public function __construct()
    {
        $this->attributeHelper = new AttributeHelper();
    }

    /**
     * @param string $file
     * @return PageRoute
     * @throws ReflectionException
     */
    public function createPage(string $file, ?Layout $layout): PageRoute
    {
        $attributes = $this->attributeHelper->getAttributes(require $file);
        return new PageRoute(
            file: $file,
            partials: $layout?->getPartials() ?? [],
            layoutFiles: $layout?->getFiles() ?? [],
            headers: array_merge($layout?->getHeaders() ?? [], $this->getHeaders($attributes)),
            meta: $this->getMeta($attributes) ?? $layout?->getMeta(),
            styles: array_merge($layout?->getStyles() ?? [new Stylesheet(__DIR__ . '/reset.css')], $this->getStyles($attributes)),
            scripts: array_merge($layout?->getScripts() ?? [], $this->getScripts($attributes)),
            lazy: $this->getLazy($attributes) ?? $layout?->getLazy(),
            reactive: $this->getReactive($attributes) ?? $layout?->getReactive(),
        );
    }


    /**
     * @param string $file
     * @param string $path
     * @return Layout
     * @throws ReflectionException
     */
    public function createLayout(string $file, string $path): Layout
    {
        $attributes = $this->attributeHelper->getAttributes(require $file);
        return new Layout(
            files: [$file],
            partials: [$path],
            headers: $this->getHeaders($attributes),
            meta: $this->getMeta($attributes),
            styles: [new Stylesheet(__DIR__ . '/reset.css'), ...$this->getStyles($attributes)],
            scripts: [new Script(__DIR__ . '/runtime.js'), ...$this->getScripts($attributes)],
            lazy: $this->getLazy($attributes),
            reactive: $this->getReactive($attributes),
        );
    }

    /**
     * @param string $file
     * @param string $path
     * @param Layout $layout
     * @return Layout
     * @throws ReflectionException
     */
    public function createNestedLayout(string $file, string $path, Layout $layout): Layout
    {
        $attributes = $this->attributeHelper->getAttributes(require $file);
        return new Layout(
            files: array_merge([$file], $layout->getFiles()),
            partials: array_merge([$path], $layout->getPartials()),
            headers: array_merge($layout->getHeaders(), $this->getHeaders($attributes)),
            meta: $this->getMeta($attributes),
            styles: array_merge($layout->getStyles(), $this->getStyles($attributes)),
            scripts: array_merge($layout->getScripts(), $this->getScripts($attributes)),
            lazy: $this->getLazy($attributes),
            reactive: $this->getReactive($attributes),
        );
    }

    /**
     * @param object[] $attributes
     * @return Header[]
     */
    private function getHeaders(array $attributes): array
    {
        $headers = [];
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Header) {
                $headers[] = $attribute;
            }
        }
        return $headers;
    }

    /**
     * @param object[] $attributes
     * @return MetaInfo|null
     */
    private function getMeta(array $attributes): ?MetaInfo
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof MetaInfo) {
                return $attribute;
            }
        }
        return null;
    }

    /**
     * @param object[] $attributes
     * @return Script[]
     */
    private function getScripts(array $attributes): array
    {
        $scripts = [];

        foreach ($attributes as $attribute) {
            if ($attribute instanceof Script) {
                $scripts[] = $attribute;
            }
        }

        return $scripts;
    }

    /**
     * @param object[] $attributes
     * @return Stylesheet[]
     */
    private function getStyles(array $attributes): array
    {
        $styles = [];

        foreach ($attributes as $attribute) {
            if ($attribute instanceof Stylesheet) {
                $styles[] = $attribute;
            }
        }

        return $styles;
    }

    /**
     * @param object[] $attributes
     * @return Reactive|null
     */
    private function getReactive(array $attributes): ?Reactive
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Reactive) {
                return $attribute;
            }
        }
        return null;
    }

    /**
     * @param object[] $attributes
     * @return Lazy|null
     */
    private function getLazy(array $attributes): ?Lazy
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Lazy) {
                return $attribute;
            }
        }
        return null;
    }
}