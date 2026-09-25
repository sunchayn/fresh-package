<?php

declare(strict_types=1);

namespace Template\Choices\PackageFeature;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class ConfigChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'config';
    }

    public function label(): string
    {
        return 'Configuration file';
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file($metadata->providerPath())->removeSectionMarkers('config');
        $chisel->file('tests/App/ExampleFunctionalTest.php')->removeSectionMarkers('config');
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file('config')->delete();
        $chisel->file('README.md')->removeMarkdownSection('Publishing the Configuration File');
        $chisel->file($metadata->providerPath())->removeSection('config');
        $chisel->file('tests/App/ExampleFunctionalTest.php')->removeSection('config');
        $chisel->file('tools/phpstan/phpstan.neon.dist')->removeLinesContaining('- ../../config');
    }
}
