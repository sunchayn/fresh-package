<?php

declare(strict_types=1);

namespace Template\Choices\PackageFeature;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class MigrationsChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'migrations';
    }

    public function label(): string
    {
        return 'Migrations';
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file($metadata->providerPath())->removeSectionMarkers('migrations');
        $chisel->file($metadata->providerPath())->removeSectionMarkers('any-features');
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file('database/migrations')->delete();
        $chisel->file('README.md')->removeMarkdownSection('Publishing and Running the Migrations');
        $chisel->file($metadata->providerPath())->removeSection('migrations');
        $chisel->file('tools/phpstan/phpstan.neon.dist')->removeLinesContaining('- ../../database');
    }
}
