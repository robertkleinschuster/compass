<?php

namespace Compass;

use Compass\Attributes\Header;
use Compass\Attributes\Lazy;
use Compass\Attributes\MetaInfo;
use Compass\Attributes\Script;
use Compass\Attributes\Stylesheet;
use Compass\Attributes\Reactive;
use Compass\Attributes\Resource;

class Attributes
{
    private ?Attributes $parent = null;

    /**
     * @param Header[] $headers
     * @param MetaInfo|null $meta
     * @param Stylesheet[] $styles
     * @param Script[] $scripts
     * @param Lazy|null $lazy
     * @param Resource|null $resource
     * @param Reactive|null $reactive
     */
    public function __construct(
        private array     $headers,
        private ?MetaInfo $meta,
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
        return new Attributes(
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

    public function getMeta(): ?MetaInfo
    {
        return $this->meta;
    }

    /**
     * @return Stylesheet[]
     */
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

    /**
     * @return Script[]
     */
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

    public function withParent(Attributes $parent): Attributes
    {
        $clone = clone $this;
        $clone->parent = $parent;
        return $clone;
    }
}