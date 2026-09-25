<?php

declare(strict_types=1);

namespace Template\Commands\Concerns;

use Template\Commands\TemplateInitCommand;
use Template\Metadata;

/**
 * @mixin TemplateInitCommand
 */
trait RewritesPlaceholders
{
    private function updateReadme(): void
    {
        if (! file_exists($this->chisel->rootDir().'/.template/stubs/repository_settings/README_PACKAGE.md')) {
            return;
        }

        $this->chisel->file('README.md')->delete();

        $this->chisel->renamePath('.template/stubs/repository_settings/README_PACKAGE.md', 'README.md');
    }

    private function updateContributingGuide(): void
    {
        if (! file_exists($this->chisel->rootDir().'/.template/stubs/repository_settings/.github/CONTRIBUTING_PACKAGE.md')) {
            return;
        }

        $this->chisel->file('.github/CONTRIBUTING.md')->delete();

        $this->chisel->renamePath('.template/stubs/repository_settings/.github/CONTRIBUTING_PACKAGE.md', '.github/CONTRIBUTING.md');
    }

    private function updateGitignore(): void
    {
        if (! file_exists($this->chisel->rootDir().'/.template/stubs/repository_settings/GITIGNORE_PACKAGE')) {
            return;
        }

        $this->chisel->file('.gitignore')->delete();

        $this->chisel->renamePath('.template/stubs/repository_settings/GITIGNORE_PACKAGE', '.gitignore');
    }

    /**
     * @return array<string, string>
     */
    private function getPlaceholderReplacements(Metadata $metadata): array
    {
        $vendorNamespace = $metadata->vendorNamespace();
        $className = $metadata->className();
        $vendorSlug = $metadata->vendorSlug();
        $packageSlug = $metadata->packageSlug();

        return [
            ':today' => date('Y-m-d'),
            ':author_name' => $metadata->authorName(),
            ':author_email' => $metadata->authorEmail(),
            ':author_username' => $vendorSlug,
            ':vendor_name' => ucwords(str_replace(['-', '_'], ' ', $vendorSlug)),
            ':vendor_slug' => $vendorSlug,
            ':package_name' => $metadata->packageNameHuman(),
            ':package_slug' => $packageSlug,
            ':package_description' => $metadata->packageDescription(),
            'vendor-name/skeleton' => $vendorSlug.'/'.$packageSlug,
            'vendor-name' => $vendorSlug,
            'Author Name' => $metadata->authorName(),
            'author@example.com' => $metadata->authorEmail(),
            'VendorName\\Skeleton' => "{$vendorNamespace}\\{$className}",
            'VendorName' => $vendorNamespace,
            'SkeletonServiceProvider' => "{$className}ServiceProvider",
            'SkeletonCommand' => "{$className}Command",
            'Skeleton' => $className,
            'skeleton_placeholder' => strtolower(str_replace('-', '_', $packageSlug)).'_placeholder',
            'skeleton' => $packageSlug,
        ];
    }

    private function renameStubFiles(Metadata $metadata): void
    {
        $className = $metadata->className();
        $packageSlug = $metadata->packageSlug();
        $tableName = strtolower(str_replace('-', '_', $packageSlug)).'_placeholder';

        $toRename = [
            'src/Skeleton.php' => "src/{$className}.php",
            'src/SkeletonServiceProvider.php' => "src/{$className}ServiceProvider.php",
            'src/Facades/Skeleton.php' => "src/Facades/{$className}.php",
            'src/Console/Commands/SkeletonCommand.php' => "src/Console/Commands/{$className}Command.php",
            'config/skeleton.php' => "config/{$packageSlug}.php",
        ];

        foreach ($toRename as $from => $to) {
            $this->chisel->renamePath($from, $to);
        }

        $migrationPaths = glob($this->chisel->rootDir().'/database/migrations/*create_skeleton_placeholder_table.php') ?: [];

        foreach ($migrationPaths as $migrationPath) {
            $destination = dirname($migrationPath).'/'.str_replace(
                'create_skeleton_placeholder_table',
                "create_{$tableName}_table",
                basename($migrationPath),
            );

            $this->chisel->renamePath(
                $this->chisel->relativePath($migrationPath),
                $this->chisel->relativePath($destination),
            );
        }
    }

    private function restoreNonPlaceholderReplacements(Metadata $metadata): void
    {
        // The workbench app only exists when the workbench choice is selected.
        if (is_file($this->chisel->rootDir().'/workbench/bootstrap/app.php')) {
            $this->chisel->file('workbench/bootstrap/app.php')
                ->replace(
                    "default_{$metadata->packageSlug()}_path",
                    'default_skeleton_path',
                );
        }

        $this->chisel->file('composer.json')
            ->replace(
                "package:purge-{$metadata->packageSlug()}",
                'package:purge-skeleton',
            );
    }
}
