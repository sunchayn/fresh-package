<?php

declare(strict_types=1);

namespace Template\Choices\RepositorySettings;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class IssueTemplateChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'issue_template';
    }

    public function label(): string
    {
        return 'Issue Template';
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->renamePath('.template/stubs/repository_settings/.github/ISSUE_TEMPLATE', '.github/ISSUE_TEMPLATE');
    }
}
