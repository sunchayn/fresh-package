<?php

declare(strict_types=1);

namespace Template\Choices\PackageFeature;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class CommandsChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'commands';
    }

    public function label(): string
    {
        return 'Commands';
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file($metadata->providerPath())->removeSectionMarkers('commands');
        $chisel->file('tests/App/ExampleFunctionalTest.php')->removeSectionMarkers('commands');
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $providerPath = $metadata->providerPath();

        $chisel->file('src/Console')->delete();

        $chisel->php($providerPath)->removeImport('SkeletonCommand')->save();

        $chisel->file($providerPath)->removeSection('commands');
        $chisel->file('tests/App/ExampleFunctionalTest.php')->removeSection('commands');
        $chisel->file('README.md')->removeLinesContaining('command');
        $chisel->file('README.md')->removeLinesContaining('Command');
    }
}
