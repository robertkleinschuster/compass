<?php

declare(strict_types=1);

namespace Compass;

readonly class RoutesCache
{
    public function __construct(private string $path)
    {
    }

    /**
     * @return Route[]|null
     */
    public function load(): ?array
    {
        $result = @include $this->path;
        if ($result === false) {
            return null;
        }
        return $result;
    }

    public function clear(): void
    {
        @unlink($this->path);
    }

    /**
     * @param Route[] $routes
     * @return void
     */
    public function save(array $routes): void
    {
        foreach ($routes as $route) {
            $route->setCache($this->path);
        }
        $data = var_export($routes, true);
        file_put_contents($this->path, <<<PHP
<?php

return $data;
PHP
        );
    }
}