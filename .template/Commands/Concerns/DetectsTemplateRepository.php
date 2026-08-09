<?php

declare(strict_types=1);

namespace Template\Commands\Concerns;

use Laravel\AgentDetector\AgentDetector;
use Template\Commands\TemplateInitCommand;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

/**
 * @mixin TemplateInitCommand
 */
trait DetectsTemplateRepository
{
    private function isNonInteractive(): bool
    {
        return $this->option('no-interaction')
            || getenv('COMPOSER_NO_INTERACTION') === '1'
            || (!defined('STDIN') || !stream_isatty(STDIN))
            || (class_exists(AgentDetector::class) && AgentDetector::detect()->isAgent);
    }

    /**
     * Tell if the command is running within the template repo or one of its forks.
     */
    private function isTemplateRepositoryOrItsFork(): bool
    {
        if (! is_dir($this->chisel->rootDir().'/.git')) {
            return false;
        }

        $result = $this->chisel->runCommand(['git', 'rev-list', '--count', 'HEAD']);

        if (! $result['success']) {
            return false;
        }

        // A repository generated from the GitHub template button starts with a single fresh commit.
        // A clone or fork of the skeleton instead carries its whole commit history,
        // so more than one commit means this is not a freshly generated repository.
        return (int) trim((string) $result['output']) > 1;
    }

    private function refuseSkeletonRepository(): void
    {
        $message = "The `template:init` command cannot run in the skeleton repository or a clone/fork of it.\n"
            .'Use `php .template/init template:sandbox <profile>` to test it, '
            .'or create your package following the README instructions.';

        if ($this->isNonInteractive()) {
            $this->printOutJson(['success' => false, 'errors' => [$message]]);

            return;
        }

        error($message);

        info('You will be prompted to selected the AI agents to configure with the repo instead.');
    }
}
