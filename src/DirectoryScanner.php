<?php

declare(strict_types=1);

namespace Compass;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

readonly class DirectoryScanner
{
    public function __construct(private string $pageFilename, private string $layoutFilename, private string $actionFilename)
    {
    }

    /**
     * @param string $directory
     * @return Route[]
     */
    public function scan(string $directory): array
    {
        $directory = rtrim($directory, DIRECTORY_SEPARATOR);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS
            )
        );
        $results = [];

        $pages = [];
        $layouts = [];
        $actions = [];

        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            if ($item->getFilename() === $this->pageFilename) {
                $pages[] = $item->getPath();
            }
            if ($item->getFilename() === $this->layoutFilename) {
                $layouts[] = $item->getPath();
            }
            if ($item->getFilename() === $this->actionFilename) {
                $actions[] = $item->getPath();
            }
        }

        usort(
            $pages,
            fn(string $a, string $b) => count(explode(DIRECTORY_SEPARATOR, $a)) <=> count(explode(DIRECTORY_SEPARATOR, $b))
        );

        $index = [];
        foreach ($pages as $i => $page) {
            $pageFile = $page . DIRECTORY_SEPARATOR . $this->pageFilename;
            $layoutFile = null;
            if (in_array($page, $layouts)) {
                $layoutFile = $page . DIRECTORY_SEPARATOR . $this->layoutFilename;
            }
            $actionFile = null;
            if (in_array($page, $actions)) {
                $actionFile = $page . DIRECTORY_SEPARATOR . $this->actionFilename;
            }
            $routePath = substr($page, strlen($directory));

            if ($routePath === '') {
                $routePath = '/';
                $results[] = new Route($routePath, null, $pageFile, $layoutFile, $actionFile);
            } else {
                $routePath = implode('/', explode(DIRECTORY_SEPARATOR, $routePath));
                $parentLevels = 1;

                do {
                    $parentPath = dirname($routePath, $parentLevels);
                    $parentLevels++;
                } while ((!isset($index[$parentPath]) || !isset($results[$index[$parentPath]])) && $parentPath !== '/');

                $results[] = new Route($routePath, $results[$index[$parentPath]] ?? null, $pageFile, $layoutFile, $actionFile);
            }

            $index[$routePath] = $i;
        }


        return $results;
    }
}