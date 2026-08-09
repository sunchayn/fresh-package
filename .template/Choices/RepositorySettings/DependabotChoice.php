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
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file('.github/dependabot.yml')->delete();
        $chisel->file('README.md')->removeLinesContaining('Dependabot');
    }

    #[\Override]
    public function manualSteps(): array
    {
        return [
            'Review Dependabot pull requests before merging. This package does not include an automatic merge workflow.',
        ];
    }
}
