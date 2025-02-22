<?php

declare(strict_types=1);

namespace Compass;

use Stringable;

final class Route implements Stringable
{
    private ?string $cache = null;

    public function __construct(
        private readonly ?string     $path = null,
        private readonly ?Route      $parent = null,
        private readonly ?string     $pageFile = null,
        private readonly ?Attributes $pageAttributes = null,
        private readonly ?string     $pageStylesheetFile = null,
        private readonly ?string     $pageStylesheetPath = null,
        private readonly ?string     $pageScriptFile = null,
        private readonly ?string     $pageScriptPath = null,
        private readonly ?string     $layoutFile = null,
        private readonly ?Attributes $layoutAttributes = null,
        private readonly ?string     $layoutStylesheetFile = null,
        private readonly ?string     $layoutStylesheetPath = null,
        private readonly ?string     $layoutScriptFile = null,
        private readonly ?string     $layoutScriptPath = null,
        private readonly ?string     $actionFile = null,
    )
    {
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
            pageFile: $data['pageFile'],
            pageAttributes: $data['pageAttributes'],
            pageStylesheetFile: $data['pageStylesheetFile'],
            pageStylesheetPath: $data['pageStylesheetPath'],
            pageScriptFile: $data['pageScriptFile'],
            pageScriptPath: $data['pageScriptPath'],
            layoutFile: $data['layoutFile'],
            layoutAttributes: $data['layoutAttributes'],
            layoutStylesheetFile: $data['layoutStylesheetFile'],
            layoutStylesheetPath: $data['layoutStylesheetPath'],
            layoutScriptFile: $data['layoutScriptFile'],
            layoutScriptPath: $data['layoutScriptPath'],
            actionFile: $data['actionFile'],
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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getParent(): ?Route
    {
        return $this->parent;
    }

    public function getPageFile(): ?string
    {
        return $this->pageFile;
    }

    public function getPageAttributes(): ?Attributes
    {
        return $this->pageAttributes;
    }

    public function getPageStylesheetFile(): ?string
    {
        return $this->pageStylesheetFile;
    }

    public function getPageStylesheetPath(): ?string
    {
        return $this->pageStylesheetPath;
    }

    public function getPageScriptFile(): ?string
    {
        return $this->pageScriptFile;
    }

    public function getPageScriptPath(): ?string
    {
        return $this->pageScriptPath;
    }

    public function getLayoutFile(): ?string
    {
        return $this->layoutFile;
    }

    public function getLayoutAttributes(): ?Attributes
    {
        if ($this->parent?->layoutAttributes !== null && $this->layoutAttributes !== null) {
            return $this->layoutAttributes->withParent($this->parent->getLayoutAttributes());
        }
        return $this->layoutAttributes;
    }

    public function getLayoutStylesheetFile(): ?string
    {
        return $this->layoutStylesheetFile;
    }

    public function getLayoutStylesheetPath(): ?string
    {
        return $this->layoutStylesheetPath;
    }

    public function getLayoutScriptFile(): ?string
    {
        return $this->layoutScriptFile;
    }

    public function getLayoutScriptPath(): ?string
    {
        return $this->layoutScriptPath;
    }

    public function getActionFile(): ?string
    {
        return $this->actionFile;
    }

    /**
     * @return string[]
     */
    public function getLayoutScripts(): array
    {
        $scripts = [];
        if ($this->getParent() !== null) {
            $scripts = $this->getParent()->getLayoutScripts();
        }
        if ($this->getLayoutScriptPath() !== null) {
            $scripts[] = $this->getLayoutScriptPath();
        }
        return $scripts;
    }

    /**
     * @return string[]
     */
    public function getLayoutStylesheets(): array
    {
        $stylesheets = [];
        if ($this->getParent() !== null) {
            $stylesheets = $this->getParent()->getLayoutStylesheets();
        }
        if ($this->getLayoutStylesheetPath() !== null) {
            $stylesheets[] = $this->getLayoutStylesheetPath();
        }
        return $stylesheets;
    }

    public function __toString(): string
    {
        return $this->getPath() ?? '';
    }
}