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
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file('.github/ISSUE_TEMPLATE')->delete();
    }
}
