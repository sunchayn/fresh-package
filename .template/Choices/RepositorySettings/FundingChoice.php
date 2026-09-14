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
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->renamePath('.template/stubs/repository_settings/.github/FUNDING.yml', '.github/FUNDING.yml');
    }
}
