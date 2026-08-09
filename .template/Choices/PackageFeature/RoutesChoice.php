<?php

declare(strict_types=1);

namespace Template\Choices\PackageFeature;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class RoutesChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'routes';
    }

    public function label(): string
    {
        return 'Routes';
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file($metadata->providerPath())->removeSectionMarkers('routes');
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file('routes')->delete();
        $chisel->file($metadata->providerPath())->removeSection('routes');
        $chisel->file('README.md')->removeLinesContaining('route');
        $chisel->file('README.md')->removeLinesContaining('Route');
        $chisel->file('tools/phpstan/phpstan.neon.dist')->removeLinesContaining('- ../../routes');
    }
}
