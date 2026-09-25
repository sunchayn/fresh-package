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

    private const array AI_FILES = [
        '.ai/GUIDELINES.md',
        '.ai/skills/scaffold-module/SKILL.md',
        '.ai/skills/task-finalization/SKILL.md',
        '.ai/skills/write-php-test/SKILL.md',
    ];

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

        // The .ai files only exist when ai_support is selected.
        if (is_dir($chisel->rootDir().'/.ai')) {
            $chisel->files(...self::AI_FILES)->removeSectionMarkers('workbench');
        }
    }

    #[\Override]
    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        // The .ai files only exist when ai_support is selected.
        if (is_dir($chisel->rootDir().'/.ai')) {
            $chisel->files(...self::AI_FILES)->removeSection('workbench');
        }
    }
}
