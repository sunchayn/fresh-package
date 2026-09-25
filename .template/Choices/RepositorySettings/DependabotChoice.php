<?php

declare(strict_types=1);

namespace Template\Choices\RepositorySettings;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class DependabotChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'dependabot';
    }

    public function label(): string
    {
        return 'Dependabot';
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->renamePath('.template/stubs/repository_settings/.github/dependabot.yml', '.github/dependabot.yml');
    }

    #[\Override]
    public function manualSteps(): array
    {
        return [
            'Review Dependabot pull requests before merging. This package does not include an automatic merge workflow.',
        ];
    }
}
