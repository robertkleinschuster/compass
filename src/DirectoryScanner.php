<?php

declare(strict_types=1);

namespace Compass;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use SplFileInfo;

readonly class DirectoryScanner
{
    private AttributesFactory $pageInfoFactory;

    public function __construct(
        private string $pageFilename,
        private string $pageStylesheetFilename,
        private string $pageScriptFilename,
        private string $layoutFilename,
        private string $layoutStylesheetFilename,
        private string $layoutScriptFilename,
        private string $actionFilename,
    )
    {
        $this->pageInfoFactory = new AttributesFactory();
    }

    /**
     * @param string[] $directories
     * @return Route[]
     * @throws ReflectionException
     */
    public function scan(array $directories): array
    {
        $pages = [];
        $layouts = [];
        $layoutStylesheets = [];
        $layoutScripts = [];
        $actions = [];
        $stylesheets = [];
        $scripts = [];
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
                $paths[] = $path;
                if ($item->getFilename() === $this->pageFilename) {
                    $pages[$path] = $item->getPathname();
                }
                if ($item->getFilename() === $this->layoutFilename) {
                    $layouts[$path] = $item->getPathname();
                }
                if ($item->getFilename() === $this->layoutStylesheetFilename) {
                    $layoutStylesheets[$path] = $item->getPathname();
                }
                if ($item->getFilename() === $this->layoutScriptFilename) {
                    $layoutScripts[$path] = $item->getPathname();
                }
                if ($item->getFilename() === $this->actionFilename) {
                    $actions[$path] = $item->getPathname();
                }
                if ($item->getFilename() === $this->pageStylesheetFilename) {
                    $stylesheets[$path] = $item->getPathname();
                }
                if ($item->getFilename() === $this->pageScriptFilename) {
                    $scripts[$path] = $item->getPathname();
                }
            }
        }

        $paths = array_unique($paths);

        usort(
            $paths,
            fn(string $a, string $b) => count(explode(DIRECTORY_SEPARATOR, $a)) <=> count(explode(DIRECTORY_SEPARATOR, $b))
        );

        $results = [];
        $index = [];
        foreach ($paths as $i => $path) {
            $pageFile = $pages[$path] ?? null;
            $layoutFile = $layouts[$path] ?? null;
            $layoutStylesheetFile = $layoutStylesheets[$path] ?? null;
            $layoutScriptFile = $layoutScripts[$path] ?? null;
            $actionFile = $actions[$path] ?? null;
            $pageStylesheetFile = $stylesheets[$path] ?? null;
            $pageScriptFile = $scripts[$path] ?? null;
            $pageStylesheetPath = null;
            if ($pageStylesheetFile !== null) {
                $hash = hash_file('crc32c', $pageStylesheetFile);
                if ($hash === '00000000') {
                    $pageStylesheetFile = null;
                } else {
                    $pageStylesheetPath = "$path/$hash.css";
                }
            }
            $pageScriptPath = null;
            if ($pageScriptFile !== null) {
                $hash = hash_file('crc32c', $pageScriptFile);
                if ($hash === '00000000') {
                    $pageScriptFile = null;
                } else {
                    $pageScriptPath = "$path/$hash.js";
                }
            }

            $layoutStylesheetPath = null;
            if ($layoutStylesheetFile !== null) {
                $hash = hash_file('crc32c', $layoutStylesheetFile);
                if ($hash === '00000000') {
                    $layoutStylesheetFile = null;
                } else {
                    $layoutStylesheetPath = "$path/$hash.css";
                }
            }
            $layoutScriptPath = null;
            if ($layoutScriptFile !== null) {
                $hash = hash_file('crc32c', $layoutScriptFile);
                if ($hash === '00000000') {
                    $layoutScriptFile = null;
                } else {
                    $layoutScriptPath = "$path/$hash.js";
                }
            }

            $pageAttributes = isset($pageFile) ? $this->pageInfoFactory->create(require $pageFile) : null;
            $layoutAttributes = isset($layoutFile) ? $this->pageInfoFactory->create(require $layoutFile) : null;

            if ($path === '') {
                $path = '/';
                $results[] = new Route(
                    path: $path,
                    pageFile: $pageFile,
                    pageAttributes: $pageAttributes,
                    pageStylesheetFile: $pageStylesheetFile,
                    pageStylesheetPath: $pageStylesheetPath,
                    pageScriptFile: $pageScriptFile,
                    pageScriptPath: $pageScriptPath,
                    layoutFile: $layoutFile,
                    layoutAttributes: $layoutAttributes,
                    layoutStylesheetFile: $layoutStylesheetFile,
                    layoutStylesheetPath: $layoutStylesheetPath,
                    layoutScriptFile: $layoutScriptFile,
                    layoutScriptPath: $layoutScriptPath,
                    actionFile: $actionFile
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
                    parent: isset($index[$parentPath]) ? $results[$index[$parentPath]] ?? null : null,
                    pageFile: $pageFile,
                    pageAttributes: $pageAttributes,
                    pageStylesheetFile: $pageStylesheetFile,
                    pageStylesheetPath: $pageStylesheetPath,
                    pageScriptFile: $pageScriptFile,
                    pageScriptPath: $pageScriptPath,
                    layoutFile: $layoutFile,
                    layoutAttributes: $layoutAttributes,
                    layoutStylesheetFile: $layoutStylesheetFile,
                    layoutStylesheetPath: $layoutStylesheetPath,
                    layoutScriptFile: $layoutScriptFile,
                    layoutScriptPath: $layoutScriptPath,
                    actionFile: $actionFile
                );
            }

            $index[$path] = $i;
        }


        return $results;
    }
}