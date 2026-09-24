<?php

declare(strict_types=1);

namespace Template\Commands\Concerns;

use JsonException;
use Template\Choices\PackageFeature\AiSupportChoice;
use Template\Choices\PackageFeature\WorkbenchChoice;
use Template\Commands\TemplateInitCommand;
use Template\Metadata;

/**
 * @mixin TemplateInitCommand
 */
trait UpdatesComposerFile
{
    /**
     * @param  list<string>  $selectedChoiceKeys Keys chosen by the user, or resolved from flags.
     * @throws JsonException When the existing composer.json file contains invalid JSON.
     */
    private function updateComposerFile(Metadata $metadata, array $selectedChoiceKeys): void
    {
        $path = $this->chisel->rootDir().'/composer.json';

        $composer = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        $namespace = $metadata->vendorNamespace().'\\'.$metadata->className().'\\';

        $composer = $this->setComposerIdentity($composer, $metadata);
        $composer = $this->setComposerAutoload($composer, $namespace, $selectedChoiceKeys);
        $composer = $this->setComposerLaravelExtra($composer, $metadata, $namespace, $selectedChoiceKeys);
        $composer = $this->setComposerScripts($composer, $selectedChoiceKeys);

        file_put_contents($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
    }

    /**
     * @param  array<string, mixed>  $composer Decoded composer.json contents.
     * @return array<string, mixed> The same contents, with identity fields set.
     */
    private function setComposerIdentity(array $composer, Metadata $metadata): array
    {
        $composer['name'] = $metadata->packageName();

        $composer['description'] = $metadata->packageDescription();

        $composer['keywords'] = array_values(array_unique([
            'laravel',
            ...explode('/', $metadata->packageName()),
        ]));

        $composer['homepage'] = 'https://github.com/'.$metadata->packageName();

        $composer['authors'] = [[
            'name' => $metadata->authorName(),
            'email' => $metadata->authorEmail(),
            'role' => 'Developer',
        ]];

        return $composer;
    }

    /**
     * @param  array<string, mixed>  $composer Decoded composer.json contents.
     * @param  list<string>  $selectedChoiceKeys Keys chosen by the user, or resolved from flags.
     * @return array<string, mixed> The same contents, with autoload rules set.
     */
    private function setComposerAutoload(array $composer, string $namespace, array $selectedChoiceKeys): array
    {
        $composer['autoload']['psr-4'] = [$namespace => 'src/'];

        $composer['autoload-dev']['psr-4'] = [$namespace.'Tests\\' => 'tests/']
            + array_filter(
                $composer['autoload-dev']['psr-4'] ?? [],
                fn (string $key): bool => ! str_starts_with($key, 'VendorName\\Skeleton\\') && $key !== 'Template\\',
                ARRAY_FILTER_USE_KEY,
            );

        if (in_array(WorkbenchChoice::key(), $selectedChoiceKeys, true)) {
            $composer['autoload-dev']['psr-4'] += [
                'Workbench\\App\\' => 'workbench/app/',
                'Workbench\\Database\\Factories\\' => 'workbench/database/factories/',
                'Workbench\\Database\\Seeders\\' => 'workbench/database/seeders/',
            ];
        }

        return $composer;
    }

    /**
     * @param  array<string, mixed>  $composer Decoded composer.json contents.
     * @param  list<string>  $selectedChoiceKeys Keys chosen by the user, or resolved from flags.
     * @return array<string, mixed> The same contents, with the laravel extra block set.
     */
    private function setComposerLaravelExtra(array $composer, Metadata $metadata, string $namespace, array $selectedChoiceKeys): array
    {
        $composer['extra']['laravel']['providers'] = [
            rtrim($namespace, '\\').'\\'.$metadata->className().'ServiceProvider',
        ];

        if (in_array('facade', $selectedChoiceKeys, true)) {
            $composer['extra']['laravel']['aliases'] = [
                $metadata->className() => rtrim($namespace, '\\').'\\Facades\\'.$metadata->className(),
            ];
        } else {
            unset($composer['extra']['laravel']['aliases']);
        }

        return $composer;
    }

    /**
     * @param  array<string, mixed>  $composer Decoded composer.json contents.
     * @param  list<string>  $selectedChoiceKeys Keys chosen by the user, or resolved from flags.
     * @return array<string, mixed> The same contents, with scripts wired to selected choices.
     */
    private function setComposerScripts(array $composer, array $selectedChoiceKeys): array
    {
        foreach (['post-install-cmd', 'post-update-cmd'] as $hook) {
            $commands = array_values(
                array_filter(
                    $composer['scripts'][$hook] ?? [],
                    fn (string $command): bool => $command !== '@php .template/init',
                ),
            );

            if ($commands === []) {
                unset($composer['scripts'][$hook]);

                continue;
            }

            $composer['scripts'][$hook] = $commands;
        }

        if (in_array(WorkbenchChoice::key(), $selectedChoiceKeys, true)) {
            $composer['scripts']['build'] = ['@php vendor/bin/testbench workbench:build --ansi'];
            $composer['scripts']['serve'] = [
                'Composer\\Config::disableProcessTimeout',
                '@build',
                '@php vendor/bin/testbench serve --ansi',
            ];
        }

        if (in_array(AiSupportChoice::key(), $selectedChoiceKeys, true)) {
            $composer = $this->addScriptToHook($composer, 'post-install-cmd', 'npx agenteq init --yes --skip-in-ci');

            return $this->addScriptToHook($composer, 'post-update-cmd', 'npx agenteq sync --yes --skip-in-ci');
        }

        return $composer;
    }

    /**
     * @param  array<string, mixed>  $composer Decoded composer.json contents.
     * @return array<string, mixed> The same contents, with the command appended to the hook.
     */
    private function addScriptToHook(array $composer, string $hook, string $command): array
    {
        /** @var list<string> $commands */
        $commands = $composer['scripts'][$hook] ?? [];

        if (! in_array($command, $commands, true)) {
            $commands[] = $command;
        }

        $composer['scripts'][$hook] = $commands;

        return $composer;
    }
}
