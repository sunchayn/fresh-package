<?php

declare(strict_types=1);

namespace Template\Commands;

use Illuminate\Console\Command;
use Illuminate\Process\Factory as ProcessFactory;
use RuntimeException;
use Template\Commands\Concerns\MirrorsProjectFiles;
use Throwable;
use function exec;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;

/**
 * Dev-only, template-authoring tool that verifies the package configuration command across profiles.
 * It mirrors the repo into .template/.sandbox/<profile> and runs the whole init command there, so we don't mutate the main working tree.
 */
class TemplateSandboxCommand extends Command
{
    use MirrorsProjectFiles;

    protected $signature = 'template:sandbox
        {profile? : Profile to run, see the PROFILES constant for the full list, or pass interactive. Omit to be prompted.}
        {--matrix : Run all defined profiles in sequence.}
        {--parallel : Run all defined profiles at once, in as many processes as there are CPU cores.}';

    protected $description = 'Run template:init inside a mirrored sandbox copy, for verifying the template itself.';

    /**
     * @var array<string, list<string>>
     */
    private const array PROFILES = [
        'backend-only' => ['--no-vue'],
        'with-frontend' => ['--vue', '--no-blade'],
        'partial' => [
            '--config',
            '--routes',
            '--facade',
            '--migrations',
            '--commands',
            '--auto-release',
            '--security-policy',
            '--no-blade',
            '--no-translations',
            '--no-assets',
            '--no-ai-support',
            '--no-boost-skill',
            '--no-vue',
            '--no-dependabot',
            '--no-issue-template',
            '--no-funding',
            // Keeps commands selected while workbench is declined.
            // Larastan needs testbench.yaml's providers key here to reflect the command signature.
            '--no-workbench',
        ],
        'all-in' => [
            '--config',
            '--routes',
            '--translations',
            '--commands',
            '--migrations',
            '--facade',
            '--assets',
            '--ai-support',
            '--boost-skill',
            '--blade',
            '--no-vue',
            '--dependabot',
            '--issue-template',
            '--auto-release',
            '--funding',
            '--security-policy',
        ],
        'all-out' => [
            '--no-config',
            '--no-routes',
            '--no-translations',
            '--no-commands',
            '--no-migrations',
            '--no-facade',
            '--no-assets',
            '--no-ai-support',
            '--no-boost-skill',
            '--no-blade',
            '--no-vue',
            '--no-dependabot',
            '--no-issue-template',
            '--no-auto-release',
            '--no-funding',
            '--no-security-policy',
            '--no-workbench',
        ],
        // Isolates the BladeFrontendChoice dependsOn AiSupportChoice ordering.
        // Verifies the mutation (related to AI files) that happens on onDecline for both frontend profiles.
        'blade-declined-alone' => ['--ai-support', '--no-blade', '--no-vue'],
    ];

    private const string INTERACTIVE_PROFILE = 'interactive';

    public function handle(): int
    {
        $rootDir = rtrim(getcwd() ?: '.', '/');

        if ($this->option('parallel')) {
            return $this->runParallel($rootDir);
        }

        if ($this->option('matrix')) {
            return $this->runMatrix($rootDir);
        }

        $profile = $this->argument('profile') ?? $this->promptForProfile();

        if (! is_string($profile) || (! isset(self::PROFILES[$profile]) && $profile !== self::INTERACTIVE_PROFILE)) {
            error('Unknown profile. Available: '.implode(', ', [self::INTERACTIVE_PROFILE, ...array_keys(self::PROFILES)]).' (or --matrix)');

            return self::FAILURE;
        }

        try {
            $this->runProfile($rootDir, $profile, self::PROFILES[$profile] ?? []);

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            error($throwable->getMessage());

            return self::FAILURE;
        }
    }

    private function runMatrix(string $rootDir): int
    {
        $failures = [];

        foreach (array_keys(self::PROFILES) as $name) {
            try {
                $this->runProfile($rootDir, $name, self::PROFILES[$name]);
            } catch (Throwable $throwable) {
                $failures[$name] = $throwable->getMessage();
                error("FAILED: {$name}");
                note($throwable->getMessage());
            }
        }

        if ($failures === []) {
            outro('All profiles passed.');

            return self::SUCCESS;
        }

        error('Failed profiles: '.implode(', ', array_keys($failures)));

        return self::FAILURE;
    }

    private function runParallel(string $rootDir): int
    {
        $processFactory = new ProcessFactory;

        $cpuCores = $this->cpuCores();

        $pending = array_keys(self::PROFILES);
        $running = [];
        $failures = [];

        info('Running '.count($pending)." profiles, up to {$cpuCores} at a time.");

        while ($pending !== [] || $running !== []) {
            while ($pending !== [] && count($running) < $cpuCores) {
                $name = array_shift($pending);

                $running[$name] = $processFactory
                    ->newPendingProcess()
                    ->path($rootDir)
                    ->forever()
                    ->start(['php', '.template/init', 'template:sandbox', $name, '--no-ansi', '--no-interaction']);
            }

            foreach ($running as $name => $process) {
                if ($process->running()) {
                    continue;
                }

                unset($running[$name]);

                $result = $process->wait();

                if ($result->successful()) {
                    info("SUCCESS: {$name}");

                    continue;
                }

                error("FAILED: {$name}");

                $failures[$name] = $result->output().$result->errorOutput();

                note($failures[$name]);
            }

            usleep(200_000);
        }

        if ($failures === []) {
            outro('All profiles passed.');

            return self::SUCCESS;
        }

        error('Failed profiles: '.implode(', ', array_keys($failures)));

        return self::FAILURE;
    }

    private function cpuCores(): int
    {
        $cores = (int) trim((string) shell_exec(PHP_OS_FAMILY === 'Darwin' ? 'sysctl -n hw.ncpu' : 'nproc'));

        return max(1, $cores);
    }

    private function promptForProfile(): int|string
    {
        return select(
            label: 'Which profile would you like to run?',
            options: [
                self::INTERACTIVE_PROFILE => 'Interactive (answer template:init prompts yourself)',
                ...array_combine(array_keys(self::PROFILES), array_keys(self::PROFILES)),
            ],
            default: self::INTERACTIVE_PROFILE,
        );
    }

    /**
     * @param  list<string>  $options
     */
    private function runProfile(string $rootDir, string $name, array $options): void
    {
        info("Running profile: {$name}");

        $sandboxDir = "{$rootDir}/.template/.sandbox/{$name}";

        spin(fn () => $this->prepareSandbox($rootDir, $sandboxDir), 'Mirroring project to sandbox...');

        spin(fn (): string => $this->exec('composer install --no-scripts --quiet', $sandboxDir), 'Installing dependencies...');

        if ($name === self::INTERACTIVE_PROFILE) {
            $this->passthru($sandboxDir);
        } else {
            $configureOutput = spin(
                fn (): string => $this->exec('php .template/init template:init --no-interaction --safe '.implode(' ', $options), $sandboxDir),
                'Configuring the package...',
            );

            note($configureOutput);
        }

        spin(fn (): string => $this->exec('composer test', $sandboxDir), 'Running tests...');
        spin(fn (): string => $this->exec('composer style:check', $sandboxDir), 'Checking code style...');
        spin(fn (): string => $this->exec('composer analyse', $sandboxDir), 'Running static analysis...');
        spin(fn (): string => $this->exec('composer rector:check', $sandboxDir), 'Checking rector rules...');

        outro("SUCCESS: {$name}");
    }

    private function exec(string $command, string $cwd): string
    {
        $originalCwd = getcwd();

        chdir($cwd);

        exec($command.' 2>&1', $outputLines, $result);

        chdir($originalCwd ?: '.');

        $output = implode("\n", $outputLines);

        if ($result !== 0) {
            throw new RuntimeException("Command failed with exit code {$result}: {$command}\n\n{$output}");
        }

        return $output;
    }

    private function passthru(string $sandboxDir): void
    {
        $process = proc_open(
            ['php', '.template/init', 'template:init', '--safe'],
            [0 => STDIN, 1 => STDOUT, 2 => STDERR],
            $pipes, $sandboxDir,
        );

        if (! is_resource($process)) {
            throw new RuntimeException('Unable to start template:init inside the sandbox.');
        }

        $result = proc_close($process);

        if ($result !== 0) {
            throw new RuntimeException("template:init exited with status {$result} inside the sandbox.");
        }
    }
}
