<?php

declare(strict_types=1);

namespace Template\Choices\PackageFeature;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class BladeFrontendChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'blade';
    }

    public function label(): string
    {
        return 'Blade Views';
    }

    #[\Override]
    public function dependsOn(): array
    {
        // AiSupportChoice must copy the .ai/ stub first,
        // or this decline's edit to a file inside it is a silent no-op on a path that does not exist yet.
        return [AiSupportChoice::class];
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file($metadata->providerPath())->removeSectionMarkers('views');
        $chisel->file($metadata->providerPath())->removeSectionMarkers('any-features');
        $chisel->file('tests/App/ExampleFunctionalTest.php')->removeSectionMarkers('views');
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file('resources/views/placeholder.blade.php')->delete();
        $chisel->file('tests/App/ExampleFunctionalTest.php')->removeSection('views');

        $chisel->file('.ai/skills/scaffold-module/SKILL.md')->removeLinesContaining('resources/views');
    }
}
