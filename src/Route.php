<?php

declare(strict_types=1);

namespace Compass;

use Stringable;

final class Route implements Stringable
{
    private ?string $cache = null;

    public function __construct(
        private readonly ?string    $path = null,
        private readonly ?PageRoute $page = null,
        private readonly ?string    $actionFile = null,
    )
    {
    }

    /**
     * @param array<string, mixed> $data
     * @return Route
     */
    public static function __set_state(array $data): Route
    {
        $route = new Route(
            path: $data['path'],
            page: $data['page'],
            actionFile: $data['actionFile'],
        );

        $route->setCache($data['cache']);

        return $route;
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

    public function getPage(): ?PageRoute
    {
        return $this->page;
    }

    public function getActionFile(): ?string
    {
        return $this->actionFile;
    }

    public function __toString(): string
    {
        return $this->getPath() ?? '';
    }
}