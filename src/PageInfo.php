<?php

namespace Compass;

use Compass\Attributes\Header;
use Compass\Attributes\Lazy;
use Compass\Attributes\PageMeta;
use Compass\Attributes\PageScript;
use Compass\Attributes\PageStyle;
use Compass\Attributes\Reactive;
use Compass\Attributes\Resource;

class PageInfo
{
    private ?PageInfo $parent = null;

    /**
     * @param Header[] $headers
     * @param PageMeta|null $meta
     * @param PageStyle[] $styles
     * @param PageScript[] $scripts
     * @param Lazy|null $lazy
     * @param Resource|null $resource
     * @param Reactive|null $reactive
     */
    public function __construct(
        private array     $headers,
        private ?PageMeta $meta,
        private array     $styles,
        private array     $scripts,
        private ?Lazy     $lazy,
        private ?Resource $resource,
        private ?Reactive $reactive,
    )
    {
    }

    public static function __set_state(array $data): object
    {
        return new PageInfo(
          headers: $data['headers'],
          meta: $data['meta'],
          styles: $data['styles'],
          scripts: $data['scripts'],
          lazy: $data['lazy'],
          resource: $data['resource'],
          reactive: $data['reactive']
        );
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getMeta(): ?PageMeta
    {
        return $this->meta;
    }

    public function getStyles(): array
    {
        if ($this->parent) {
            return [
                ...$this->parent->getStyles(),
                ...$this->styles
            ];
        } else {
            return $this->styles;
        }
    }

    public function getScripts(): array
    {
        if ($this->parent) {
            return [
                ...$this->parent->getScripts(),
                ...$this->scripts
            ];
        } else {
            return $this->scripts;
        }
    }

    public function getLazy(): ?Lazy
    {
        return $this->lazy;
    }

    public function getResource(): ?Resource
    {
        return $this->resource;
    }

    public function getReactive(): ?Reactive
    {
        return $this->reactive;
    }

    public function setParent(PageInfo $parent): void
    {
        $this->parent = $parent;
    }
}