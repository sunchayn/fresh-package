<?php

declare(strict_types=1);

namespace Template\Commands\Concerns;

use FilesystemIterator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Template\Commands\TemplateInitCommand;

/**
 * @mixin TemplateInitCommand
 */
trait FinishesInitialization
{
    private function postInitializationCleanups(): void
    {
        $this->chisel->file('.github/workflows/verify-template.yml')->delete();

        $this->cleanupEmptyDirectories();

        $this->chisel->runCommand([
            ...$this->composerCommand(),
            'remove', '--dev', '--no-install', '--no-scripts', '--no-audit', '--quiet',
            'laravel/agent-detector', 'sunchayn/chisel-extended', 'laravel/prompts',
        ]);

        $this->chisel->file('tools/phpstan/phpstan.neon.dist')
            ->removeLinesContaining('- ../../.template/Choices')
            ->removeLinesContaining('- ../../.template/Commands')
            ->removeLinesContaining('- ../../.template/Contracts')
            ->removeLinesContaining('- ../../.template/Metadata.php');

        $this->chisel->file('tools/rector/config.php')
            ->removeLinesContaining("__DIR__.'/../../.template',");

        $this->chisel->file('.template')->delete();
    }

    private function postInitializationCommands(): void
    {
        $this->chisel->runCommand([PHP_BINARY, 'vendor/bin/pint']);

        $this->chisel->runCommand([...$this->composerCommand(), 'dump-autoload', '--quiet']);

        $this->initializeAiGuidelines();
    }

    /**
     * Syncs AI config files via agenteq whenever a `.ai` directory is present.
     */
    private function initializeAiGuidelines(): void
    {
        if (! is_dir($this->chisel->rootDir().'/.ai')) {
            return;
        }

        if ($this->isNonInteractive()) {
            $this->chisel->runCommand(['npx', 'agenteq', 'sync', '--yes']);

            return;
        }

        $process = proc_open(
            ['npx', 'agenteq', 'init'],
            [0 => STDIN, 1 => STDOUT, 2 => STDERR],
            $pipes, $this->chisel->rootDir(),
        );

        if (is_resource($process)) {
            proc_close($process);
        }
    }

    /**
     * @param  list<string>  $choices Keys of the choices the user selected.
     * @return list<string> Manual follow-up steps from just those choices.
     */
    private function manualSteps(array $choices): array
    {
        $steps = [];

        foreach ($this->choices as $choice) {
            if (in_array($choice::key(), $choices, true)) {
                $steps = [
                    ...$steps,
                    ...$choice->manualSteps(),
                ];
            }
        }

        return $steps;
    }

    /**
     * Scans the whole tree bottom-up and removes any directory a choice's onDecline left empty.
     */
    private function cleanupEmptyDirectories(): void
    {
        $rootDir = $this->chisel->rootDir();

        $skip = ['vendor', 'node_modules', '.git'];

        $directories = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($rootDir, FilesystemIterator::SKIP_DOTS),
                fn (SplFileInfo $file): bool => ! $file->isDir() || ! in_array($file->getFilename(), $skip, true),
            ),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($directories as $directory) {
            if ($directory->isDir() && iterator_count(new FilesystemIterator($directory->getPathname(), FilesystemIterator::SKIP_DOTS)) === 0) {
                rmdir($directory->getPathname());
            }
        }
    }

    /**
     * @return list<string>
     */
    private function composerCommand(): array
    {
        $composerBinary = getenv('COMPOSER_BINARY');

        return $composerBinary !== false ? [PHP_BINARY, $composerBinary] : ['composer'];
    }
}
