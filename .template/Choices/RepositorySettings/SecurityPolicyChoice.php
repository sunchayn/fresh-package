<?php

declare(strict_types=1);

namespace Template\Choices\RepositorySettings;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class SecurityPolicyChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'security_policy';
    }

    public function label(): string
    {
        return 'Security Policy';
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->renamePath('.template/stubs/repository_settings/.github/SECURITY.md', '.github/SECURITY.md');
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file('README.md')->removeLinesContaining('.github/SECURITY.md');
    }
}
