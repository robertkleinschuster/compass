<?php

namespace Compass;

use Compass\Attributes\Header;
use Compass\Attributes\Lazy;
use Compass\Attributes\MetaInfo;
use Compass\Attributes\Script;
use Compass\Attributes\Stylesheet;
use Compass\Attributes\Reactive;
use Compass\Attributes\Resource;
use Mosaic\AttributeHelper;
use ReflectionException;

class AttributesFactory
{
    private AttributeHelper $attributeHelper;


    public function __construct()
    {
        $this->attributeHelper = new AttributeHelper();
    }

    /**
     * @param mixed $page
     * @return Attributes
     * @throws ReflectionException
     */
    public function create(mixed $page): Attributes
    {
        $attributes = $this->attributeHelper->getAttributes($page);
        return new Attributes(
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
     * @return Resource|null
     */
    private function getResource(array $attributes): ?Resource
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Resource) {
                return $attribute;
            }
        }
        return null;

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