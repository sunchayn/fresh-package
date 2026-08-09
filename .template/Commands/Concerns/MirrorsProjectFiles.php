<?php

declare(strict_types=1);

namespace Template\Commands\Concerns;

use Template\Commands\TemplateSandboxCommand;

/**
 * @mixin TemplateSandboxCommand
 */
trait MirrorsProjectFiles
{
    /** @var list<string> */
    private const array EXCLUDED = [
        '.template/.sandbox',
        'vendor',
        'node_modules',
        '.git',
        '.ai',
        'composer.lock',
        '.env',
        '.idea',
        'build',
        'tools/phpstan/build',
    ];

    private function prepareSandbox(string $rootDir, string $sandboxDir): void
    {
        if (is_dir($sandboxDir)) {
            $this->deleteDirectory($sandboxDir);
        }

        mkdir($sandboxDir, 0755, true);

        $this->mirror($rootDir, $rootDir, $sandboxDir);
    }

    private function mirror(string $rootDir, string $source, string $destination): void
    {
        foreach (scandir($source) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $sourcePath = "{$source}/{$item}";

            $relativePath = ltrim(substr($sourcePath, strlen($rootDir)), '/');

            if ($this->isExcluded($relativePath)) {
                continue;
            }

            $targetPath = "{$destination}/{$item}";

            if (is_dir($sourcePath)) {
                mkdir($targetPath, 0755, true);

                $this->mirror($rootDir, $sourcePath, $targetPath);

                continue;
            }

            copy($sourcePath, $targetPath);
        }
    }

    private function isExcluded(string $relativePath): bool
    {
        foreach (self::EXCLUDED as $pattern) {
            if ($relativePath === $pattern || str_starts_with($relativePath, $pattern.'/')) {
                return true;
            }
        }

        return false;
    }

    private function deleteDirectory(string $path): void
    {
        foreach (scandir($path) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = "{$path}/{$item}";

            is_dir($itemPath) && ! is_link($itemPath)
                ? $this->deleteDirectory($itemPath)
                : unlink($itemPath);
        }

        rmdir($path);
    }
}
