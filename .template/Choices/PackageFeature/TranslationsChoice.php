<?php

declare(strict_types=1);

namespace Template\Choices\PackageFeature;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class TranslationsChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'translations';
    }

    public function label(): string
    {
        return 'Translations';
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file($metadata->providerPath())->removeSectionMarkers('translations');
        $chisel->file($metadata->providerPath())->removeSectionMarkers('any-features');
        $chisel->file('tests/App/ExampleFunctionalTest.php')->removeSectionMarkers('translations');
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file('lang')->delete();
        $chisel->file('README.md')->removeMarkdownSection('Publishing the Translations');
        $chisel->file($metadata->providerPath())->removeSection('translations');
        $chisel->file('tests/App/ExampleFunctionalTest.php')->removeSection('translations');
    }
}
