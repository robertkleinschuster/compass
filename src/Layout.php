<?php

namespace Compass;

use Compass\Attributes\Header;
use Compass\Attributes\Lazy;
use Compass\Attributes\MetaInfo;
use Compass\Attributes\Script;
use Compass\Attributes\Stylesheet;
use Compass\Attributes\Reactive;

class Layout
{
    /**
     * @param string[] $files
     * @param string[] $partials
     * @param Header[] $headers
     * @param MetaInfo|null $meta
     * @param Stylesheet[] $styles
     * @param Script[] $scripts
     * @param Lazy|null $lazy
     * @param Reactive|null $reactive
     */
    public function __construct(
        private array     $files,
        private array     $partials,
        private array     $headers,
        private ?MetaInfo $meta,
        private array     $styles,
        private array     $scripts,
        private ?Lazy     $lazy,
        private ?Reactive $reactive,
    )
    {
    }

    /**
     * @param array<string, mixed> $data
     * @return Layout
     */
    public static function __set_state(array $data): Layout
    {
        return new Layout(
            files: $data['files'],
            partials: $data['partials'],
            headers: $data['headers'],
            meta: $data['meta'],
            styles: $data['styles'],
            scripts: $data['scripts'],
            lazy: $data['lazy'],
            reactive: $data['reactive'],
        );
    }


    /**
     * @return string[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return string[]
     */
    public function getPartials(): array
    {
        return $this->partials;
    }

    /**
     * @return Header[]
     */
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
        return $this->styles;
    }

    /**
     * @return Script[]
     */
    public function getScripts(): array
    {
        return $this->scripts;
    }

    public function getLazy(): ?Lazy
    {
        return $this->lazy;
    }


    public function getReactive(): ?Reactive
    {
        return $this->reactive;
    }
}