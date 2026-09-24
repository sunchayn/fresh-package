<?php

declare(strict_types=1);

namespace Template\Choices\PackageFeature;

use Laravel\Chisel\Chisel;
use Template\Choices\AbstractChoice;
use Template\Metadata;

class WorkbenchChoice extends AbstractChoice
{
    public static function key(): string
    {
        return 'workbench';
    }

    public function label(): string
    {
        return 'Workbench (Testbench dev app)';
    }

    #[\Override]
    public function dependsOn(): array
    {
        return [AiSupportChoice::class];
    }

    #[\Override]
    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->copyDirectory('.template/stubs/workbench_choice/workbench', 'workbench');

        $chisel->renamePath('.template/stubs/workbench_choice/testbench.yaml', 'testbench.yaml');

        $chisel->file('tools/rector/config.php')->insertAfter(
            search: "__DIR__.'/../../src',",
            insertion: "        __DIR__.'/../../workbench',",
        );
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        $chisel->file('.ai/GUIDELINES.md')->removeLinesContaining('Workbench build: `composer build`');
        $chisel->file('.ai/GUIDELINES.md')->removeLinesContaining('Workbench server: `composer serve`');
        $chisel->file('.ai/GUIDELINES.md')->replace(', workbench files,', ',');

        $chisel->file('.ai/skills/scaffold-module/SKILL.md')->replace(', workbench files,', ',');

        $chisel->file('.ai/skills/task-finalization/SKILL.md')
            ->replace('`config/`, `database/`, or `workbench/`', '`config/`, or `database/`');

        $chisel->file('.ai/skills/write-php-test/SKILL.md')
            ->replace(' Test workbench behavior after running `composer build`, when needed.', '');
    }
}
