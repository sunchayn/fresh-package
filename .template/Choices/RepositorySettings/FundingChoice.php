<?php

declare(strict_types=1);

namespace Template\Choices\RepositorySettings;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class FundingChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'funding';
    }

    public function label(): string
    {
        return 'Funding';
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file('.github/FUNDING.yml')->delete();
    }
}
