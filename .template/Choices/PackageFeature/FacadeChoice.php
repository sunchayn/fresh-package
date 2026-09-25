<?php

declare(strict_types=1);

namespace Template\Choices\PackageFeature;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class FacadeChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'facade';
    }

    public function label(): string
    {
        return 'Facade';
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file('src/Facades')->delete();
    }
}
