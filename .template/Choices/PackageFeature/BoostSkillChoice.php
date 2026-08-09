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
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->files('resources/boost/skills', '.ai/skills/package-generate-skill')->delete();
    }
}
