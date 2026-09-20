<?php

declare(strict_types=1);

namespace Template\Choices\PackageFeature;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;
use Throwable;

class VueFrontendChoice extends AbstractChoice
{
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
        $chisel->file($providerPath)->removeSectionMarkers('any-features');

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
        $chisel->file('.ai/GUIDELINES.md')->removeLinesContaining('resources/js');
        $chisel->file('.ai/skills/scaffold-module/SKILL.md')->removeLinesContaining('resources/js');
        $chisel->file('.ai/skills/task-finalization/SKILL.md')->removeLinesContaining('resources/js');
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
