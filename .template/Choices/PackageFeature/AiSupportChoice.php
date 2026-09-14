<?php

declare(strict_types=1);

namespace Template\Choices\PackageFeature;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class AiSupportChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'ai_support';
    }

    public function label(): string
    {
        return 'AI Support (Guidelines, Skills, etc.)';
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->copyDirectory('.template/stubs/ai_support_choice', '.');
    }
}
