<?php

declare(strict_types=1);

namespace Template\Choices\RepositorySettings;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Choices\PackageFeature\AiSupportChoice;
use Template\Metadata;

class AutoReleaseChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'auto_release';
    }

    public function label(): string
    {
        return 'Auto Release';
    }

    #[\Override]
    public function dependsOn(): array
    {
        return [AiSupportChoice::class];
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file('CHANGELOG.md')->delete();
        $chisel->renamePath('.template/stubs/repository_settings/CHANGELOG_PACKAGE.md', 'CHANGELOG.md');

        // The package-release skill only belongs under .ai/, which only exists when ai_support is also selected.
        if (is_dir($chisel->rootDir().'/.ai')) {
            $chisel->renamePath('.template/stubs/repository_settings/.ai/skills/package-release', '.ai/skills/package-release');
        }
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel
            ->files(
                'CHANGELOG.md',
                'release-please-config.json',
                '.release-please-manifest.json',
                '.github/workflows/release.yml',
            )
            ->delete();

        $chisel->file('README.md')->removeMarkdownSection('Changelog');
        $chisel->file('README.md')->removeLinesContaining('changelog');
        $chisel->file('README.md')->removeLinesContaining('CHANGELOG');
        $chisel->file('.ai/GUIDELINES.md')->removeLinesContaining('package-release');
    }

    #[\Override]
    public function manualSteps(): array
    {
        return [
            'Commit messages should follow the Conventional Commits format so release-please can determine version bumps and changelog sections.',
        ];
    }
}
