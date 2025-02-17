<?php

namespace Compass;

use Compass\Attributes\Header;
use Compass\Attributes\Lazy;
use Compass\Attributes\PageMeta;
use Compass\Attributes\PageScript;
use Compass\Attributes\PageStyle;
use Compass\Attributes\Reactive;
use Compass\Attributes\Resource;
use Mosaic\AttributeHelper;
use ReflectionException;

class PageAttributesFactory
{
    private AttributeHelper $attributeHelper;


    public function __construct()
    {
        $this->attributeHelper = new AttributeHelper();
    }

    /**
     * @param mixed $page
     * @param string|null $style
     * @param string|null $script
     * @return PageAttributes
     * @throws ReflectionException
     */
    public function create(mixed $page): PageAttributes
    {
        $attributes = $this->attributeHelper->getAttributes($page);
        return new PageAttributes(
            headers: $this->getHeaders($attributes),
            meta: $this->getMeta($attributes),
            styles: $this->getStyles($attributes),
            scripts: $this->getScripts($attributes),
            lazy: $this->getLazy($attributes),
            resource: $this->getResource($attributes),
            reactive: $this->getReactive($attributes)
        );
    }

    /**
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
     * @return PageMeta|null
     */
    private function getMeta(array $attributes): ?PageMeta
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof PageMeta) {
                return $attribute;
            }
        }
        return null;
    }

    /**
     * @return PageScript[]
     */
    private function getScripts(array $attributes): array
    {
        $scripts = [];

        foreach ($attributes as $attribute) {
            if ($attribute instanceof PageScript) {
                $scripts[] = $attribute;
            }
        }

        return $scripts;
    }

    /**
     * @return PageStyle[]
     */
    private function getStyles(array $attributes): array
    {
        $styles = [];

        foreach ($attributes as $attribute) {
            if ($attribute instanceof PageStyle) {
                $styles[] = $attribute;
            }
        }

        return $styles;
    }

    private function getResource(array $attributes): ?Resource
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Resource) {
                return $attribute;
            }
        }
        return null;

    }

    private function getReactive(array $attributes): ?Reactive
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Reactive) {
                return $attribute;
            }
        }
        return null;
    }

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