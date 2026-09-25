<?php

declare(strict_types=1);

namespace Template\Choices\PackageFeature;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class BoostSkillChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'boost_skill';
    }

    public function label(): string
    {
        return 'Boost Skill';
    }

    #[\Override]
    public function dependsOn(): array
    {
        return [AiSupportChoice::class];
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->copyDirectory('.template/stubs/boost_skill_choice/resources', 'resources');

        $chisel->renamePath(
            'resources/boost/skills/skeleton',
            "resources/boost/skills/{$metadata->packageSlug()}-development",
        );

        // The package-generate-skill only belongs under .ai/, which only exists when ai_support is also selected.
        if (is_dir($chisel->rootDir().'/.ai')) {
            $chisel->copyDirectory('.template/stubs/boost_skill_choice/.ai', '.ai');
        }
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        // The guidelines file only exists when ai_support is selected.
        if (is_file($chisel->rootDir().'/.ai/GUIDELINES.md')) {
            $chisel->file('.ai/GUIDELINES.md')->removeLinesContaining('package-generate-skill');
        }
    }
}
