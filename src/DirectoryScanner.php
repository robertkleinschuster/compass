<?php

declare(strict_types=1);

namespace Compass;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

readonly class DirectoryScanner
{
    private PageInfoFactory $pageInfoFactory;

    public function __construct(
        private string $pageFilename,
        private string $layoutFilename,
        private string $actionFilename,
        private string $stylesheetFilename,
        private string $scriptFilename,
    )
    {
        $this->pageInfoFactory = new PageInfoFactory();
    }

    /**
     * @param string $directory
     * @return Route[]
     */
    public function scan(string $root): array
    {
        $root = rtrim($root, DIRECTORY_SEPARATOR);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $root,
                FilesystemIterator::SKIP_DOTS
            )
        );
        $results = [];

        $pages = [];
        $layouts = [];
        $actions = [];
        $stylesheets = [];
        $scripts = [];
        $directories = [];

        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            $path = substr($item->getPath(), strlen($root));
            $directories[] = $path;
            if ($item->getFilename() === $this->pageFilename) {
                $pages[$path] = $item->getPathname();
            }
            if ($item->getFilename() === $this->layoutFilename) {
                $layouts[$path] = $item->getPathname();
            }
            if ($item->getFilename() === $this->actionFilename) {
                $actions[$path] = $item->getPathname();
            }
            if ($item->getFilename() === $this->stylesheetFilename) {
                $stylesheets[$path] = $item->getPathname();
            }
            if ($item->getFilename() === $this->scriptFilename) {
                $scripts[$path] = $item->getPathname();
            }
        }

        $directories = array_unique($directories);

        usort(
            $directories,
            fn(string $a, string $b) => count(explode(DIRECTORY_SEPARATOR, $a)) <=> count(explode(DIRECTORY_SEPARATOR, $b))
        );

        $index = [];
        foreach ($directories as $i => $path) {
            $pageFile = $pages[$path] ?? null;
            $layoutFile = $layouts[$path] ?? null;
            $actionFile = $actions[$path] ?? null;
            $stylesheetFile = $stylesheets[$path] ?? null;
            $scriptFile = $scripts[$path] ?? null;
            $stylesheetPath = $stylesheetFile !== null ? ($path ?: 'styles') . '.css' : null;
            $scriptPath = $scriptFile !== null ? ($path ?: 'script') . '.js' : null;
            $pageInfo = isset($pageFile) ? $this->pageInfoFactory->create(
                require $pageFile,
                $stylesheetPath,
                $scriptPath
            ) : null;

            if ($path === '') {
                $path = '/';
                $results[] = new Route(
                    path: $path,
                    parent: null,
                    page: $pageFile,
                    layout: $layoutFile,
                    action: $actionFile,
                    stylesheet: $stylesheetFile,
                    stylesheetPath: $stylesheetPath,
                    script: $scriptFile,
                    scriptPath: $scriptPath,
                    pageInfo: $pageInfo
                );
            } else {
                $path = implode('/', explode(DIRECTORY_SEPARATOR, $path));
                $parentLevels = 1;

                do {
                    $parentPath = dirname($path, $parentLevels);
                    $parentLevels++;
                } while ((!isset($index[$parentPath]) || !isset($results[$index[$parentPath]])) && $parentPath !== '/');

                $results[] = new Route(
                    path: $path,
                    parent: $results[$index[$parentPath]] ?? null,
                    page: $pageFile,
                    layout: $layoutFile,
                    action: $actionFile,
                    stylesheet: $stylesheetFile,
                    stylesheetPath: $stylesheetPath,
                    script: $scriptFile,
                    scriptPath: $scriptPath,
                    pageInfo: $pageInfo
                );
            }

            $index[$path] = $i;
        }


        return $results;
    }
}