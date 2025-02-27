<?php

declare(strict_types=1);

namespace Compass;

use Compass\Attributes\Stylesheet;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use SplFileInfo;

readonly class DirectoryScanner
{
    private EntrypointFactory $entrypointFactory;

    public function __construct(
        private string $pageFilename,
        private string $layoutFilename,
        private string $actionFilename,
    )
    {
        $this->entrypointFactory = new EntrypointFactory();
    }

    /**
     * @param string[] $directories
     * @return Route[]
     * @throws ReflectionException
     */
    public function scan(array $directories): array
    {
        $pageFiles = [];
        $layoutFiles = [];
        $actionFiles = [];
        $paths = [];

        foreach ($directories as $directory) {
            $directory = rtrim($directory, DIRECTORY_SEPARATOR);
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $directory,
                    FilesystemIterator::SKIP_DOTS
                )
            );

            /** @var SplFileInfo $item */
            foreach ($iterator as $item) {
                $path = substr($item->getPath(), strlen($directory));
                if ($path === '') {
                    $path = '/';
                } else {
                    $path = implode('/', explode(DIRECTORY_SEPARATOR, $path));
                }
                $paths[$path] = $path;
                if ($item->getFilename() === $this->pageFilename) {
                    $pageFiles[$path] = $item->getPathname();
                }
                if ($item->getFilename() === $this->layoutFilename) {
                    $layoutFiles[$path] = $item->getPathname();
                }
                if ($item->getFilename() === $this->actionFilename) {
                    $actionFiles[$path] = $item->getPathname();
                }
            }
        }

        $paths = array_values($paths);

        usort(
            $paths,
            fn(string $a, string $b) => count(explode(DIRECTORY_SEPARATOR, $a)) <=> count(explode(DIRECTORY_SEPARATOR, $b))
        );

        $results = [];

        foreach ($paths as $path) {
            $parts = explode('/', $path);

            /** @var null|PageRoute $layout */
            $layout = null;
            $layoutPath = '/';
            do {
                $layoutPath = rtrim($layoutPath, '/') . '/' . array_shift($parts);
                if (isset($layoutFiles[$layoutPath])) {
                    if ($layout === null) {
                        $layout = $this->entrypointFactory->createLayout(
                            file: $layoutFiles[$layoutPath],
                            path: $layoutPath
                        );
                    } else {
                        $layout = $this->entrypointFactory->createNestedLayout(
                            file: $layoutFiles[$layoutPath],
                            path: $layoutPath,
                            layout: $layout
                        );
                    }
                }
            } while ($layoutPath !== $path);

            $page = null;
            if (isset($pageFiles[$path])) {
                $page = $this->entrypointFactory->createPage($pageFiles[$path], $layout);
            }

            $results[$path] = new Route(
                path: $path,
                page: $page,
                actionFile: $actionFiles[$path] ?? null
            );
        }


        return array_values($results);
    }
}