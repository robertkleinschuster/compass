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
        $pageStylesheets = [];
        $pageScripts = [];
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
                $paths[$path] = $path;
                if ($item->getFilename() === $this->pageFilename) {
                    $pages[$path] = $item->getPathname();
                }
                if ($item->getFilename() === $this->pageStylesheetFilename) {
                    $pageStylesheets[$path] = $item->getPathname();
                }
                if ($item->getFilename() === $this->pageScriptFilename) {
                    $pageScripts[$path] = $item->getPathname();
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
            }
        }

        $paths = array_values($paths);

        usort(
            $paths,
            fn(string $a, string $b) => count(explode(DIRECTORY_SEPARATOR, $a)) <=> count(explode(DIRECTORY_SEPARATOR, $b))
        );

        $results = [];

        foreach ($paths as $i => $path) {
            $pageFile = $pages[$path] ?? null;
            $pageScriptFile = $pageScripts[$path] ?? null;
            $pageStylesheetFile = $pageStylesheets[$path] ?? null;
            $layoutFile = $layouts[$path] ?? null;
            $layoutStylesheetFile = $layoutStylesheets[$path] ?? null;
            $layoutScriptFile = $layoutScripts[$path] ?? null;
            $actionFile = $actions[$path] ?? null;

            $pageStylesheetPath = $this->buildAssetPath($pageStylesheetFile, $path, 'css');
            $pageScriptPath = $this->buildAssetPath($pageScriptFile, $path, 'js');
            $layoutStylesheetPath = $this->buildAssetPath($layoutStylesheetFile, $path, 'css');
            $layoutScriptPath = $this->buildAssetPath($layoutScriptFile, $path, 'js');
            $pageAttributes = isset($pageFile) ? $this->pageInfoFactory->create(require $pageFile) : null;
            $layoutAttributes = isset($layoutFile) ? $this->pageInfoFactory->create(require $layoutFile) : null;

            if ($path === '') {
                $path = '/';
                $results[$path] = new Route(
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
                } while (!isset($results[$parentPath]) && $parentPath !== '/');

                $results[$path] = new Route(
                    path: $path,
                    parent: $results[$parentPath] ?? null,
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
        }


        return array_values($results);
    }

    private function buildAssetPath(?string $asset, string $path, string $extension): ?string
    {
        if ($asset !== null) {
            $hash = hash_file('crc32c', $asset);
            if ($hash !== '00000000') {
                return "$path/$hash.$extension";
            }
        }
        return null;
    }
}