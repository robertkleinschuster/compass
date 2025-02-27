<?php

namespace Compass;

use Compass\Attributes\Header;
use Compass\Attributes\Lazy;
use Compass\Attributes\MetaInfo;
use Compass\Attributes\Script;
use Compass\Attributes\Stylesheet;
use Compass\Attributes\Reactive;

class PageRoute
{
    /**
     * @param string $file
     * @param string[] $partials
     * @param string[] $layoutFiles
     * @param Header[] $headers
     * @param MetaInfo|null $meta
     * @param Stylesheet[] $styles
     * @param Script[] $scripts
     * @param Lazy|null $lazy
     * @param Reactive|null $reactive
     */
    public function __construct(
        private string    $file,
        private array     $partials,
        private array     $layoutFiles,
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
     * @return PageRoute
     */
    public static function __set_state(array $data): PageRoute
    {
        return new PageRoute(
            file: $data['file'],
            partials: $data['partials'],
            layoutFiles: $data['layoutFiles'],
            headers: $data['headers'],
            meta: $data['meta'],
            styles: $data['styles'],
            scripts: $data['scripts'],
            lazy: $data['lazy'],
            reactive: $data['reactive'],
        );
    }


    public function getFile(): string
    {
        return $this->file;
    }

    public function getPartials(): array
    {
        return $this->partials;
    }

    public function getLayoutFiles(): array
    {
        return $this->layoutFiles;
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