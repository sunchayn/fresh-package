<?php

declare(strict_types=1);

namespace Template\Choices\PackageFeature;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;
use Throwable;

class VueFrontendChoice extends AbstractChoice
{
    private const array AI_FILES = [
        '.ai/GUIDELINES.md',
        '.ai/skills/scaffold-module/SKILL.md',
        '.ai/skills/task-finalization/SKILL.md',
        '.ai/skills/write-comments/SKILL.md',
    ];

    private bool $installFailed = false;

    public static function key(): string
    {
        return 'vue';
    }

    public function label(): string
    {
        return 'Vue Frontend';
    }

    #[\Override]
    public function dependsOn(): array
    {
        return [AiSupportChoice::class];
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->copyDirectory('.template/stubs/vue_choice', '.');

        $providerPath = $metadata->providerPath();
        $packageSlug = $metadata->packageSlug();

        $chisel->file($providerPath)->removeSectionMarkers('views');
        $chisel->file($providerPath)->removeSectionMarkers('vue');

        // The .ai files only exist when ai_support is selected.
        if (is_dir($chisel->rootDir().'/.ai')) {
            $chisel->files(...self::AI_FILES)->removeSectionMarkers('vue');
        }

        $chisel
            ->file('routes/web.php')
            ->insertAfter(
                search: 'declare(strict_types=1);',
                insertion: "\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::view('{$packageSlug}', '{$packageSlug}::app')->name('{$packageSlug}.app');",
            );

        try {
            $chisel->npm()->install();
        } catch (Throwable) {
            $this->installFailed = true;
        }
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file($metadata->providerPath())->removeSection('vue');

        // The .ai files only exist when ai_support is selected.
        if (is_dir($chisel->rootDir().'/.ai')) {
            $chisel->files(...self::AI_FILES)->removeSection('vue');
        }
    }

    /**
     * @return list<string>
     */
    #[\Override]
    public function manualSteps(): array
    {
        return $this->installFailed
            ? ['Run your frontend package manager install manually. The automatic install during template:init failed.']
            : [];
    }
}
