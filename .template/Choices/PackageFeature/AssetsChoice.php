<?php

declare(strict_types=1);

namespace Template\Choices\PackageFeature;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class AssetsChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'assets';
    }

    public function label(): string
    {
        return 'Public Assets';
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file($metadata->providerPath())->removeSectionMarkers('assets');
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file('public')->delete();
        $chisel->file($metadata->providerPath())->removeSection('assets');
    }
}
