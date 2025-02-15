<?php

declare(strict_types=1);

namespace Compass;

use Stringable;

final class Route implements Stringable
{
    private ?string $cache = null;

    public function __construct(
        private readonly string  $path,
        private readonly ?Route  $parent,
        private readonly ?string $page,
        private readonly ?string $layout,
        private readonly ?string $action,
        private readonly ?string $stylesheet,
        private readonly ?string $stylesheetPath,
        private readonly ?string $script,
        private readonly ?string $scriptPath,
        private readonly ?PageInfo $pageInfo
    )
    {
        if ($this->pageInfo !== null && $this->parent?->getPageInfo() !== null) {
            $this->pageInfo->setParent($this->parent->getPageInfo());
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return Route
     */
    public static function __set_state(array $data): Route
    {
        return new Route(
            path: $data['path'],
            parent: $data['parent'],
            page: $data['page'],
            layout: $data['layout'],
            action: $data['action'],
            stylesheet: $data['stylesheet'],
            stylesheetPath: $data['stylesheetPath'],
            script: $data['script'],
            scriptPath: $data['scriptPath'],
            pageInfo: $data['pageInfo'],
        );
    }

    public function getCache(): ?string
    {
        return $this->cache;
    }

    public function setCache(?string $cache): void
    {
        $this->cache = $cache;
    }

    public function getName(): string
    {
        return $this->path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPage(): ?string
    {
        return $this->page;
    }

    public function getParent(): ?Route
    {
        return $this->parent;
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }

    public function hasLayout(): bool
    {
        return isset($this->layout);
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getStylesheet(): ?string
    {
        return $this->stylesheet;
    }

    public function getScript(): ?string
    {
        return $this->script;
    }

    public function getStylesheetPath(): ?string
    {
        return $this->stylesheetPath;
    }

    public function getScriptPath(): ?string
    {
        return $this->scriptPath;
    }

    public function getPageInfo(): ?PageInfo
    {
        return $this->pageInfo;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}