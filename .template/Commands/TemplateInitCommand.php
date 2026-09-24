<?php

declare(strict_types=1);

namespace Template\Commands;

use Illuminate\Console\Command;
use JsonException;
use Laravel\Chisel\Chisel;
use Laravel\Chisel\Question;
use Laravel\Chisel\Script;
use Template\Choices\PackageFeature\AiSupportChoice;
use Template\Choices\PackageFeature\AssetsChoice;
use Template\Choices\PackageFeature\BladeFrontendChoice;
use Template\Choices\PackageFeature\BoostSkillChoice;
use Template\Choices\PackageFeature\CommandsChoice;
use Template\Choices\PackageFeature\ConfigChoice;
use Template\Choices\PackageFeature\FacadeChoice;
use Template\Choices\PackageFeature\MigrationsChoice;
use Template\Choices\PackageFeature\RoutesChoice;
use Template\Choices\PackageFeature\TranslationsChoice;
use Template\Choices\PackageFeature\VueFrontendChoice;
use Template\Choices\PackageFeature\WorkbenchChoice;
use Template\Choices\RepositorySettings\AutoReleaseChoice;
use Template\Choices\RepositorySettings\DependabotChoice;
use Template\Choices\RepositorySettings\FundingChoice;
use Template\Choices\RepositorySettings\IssueTemplateChoice;
use Template\Choices\RepositorySettings\SecurityPolicyChoice;
use Template\Commands\Concerns\DetectsTemplateRepository;
use Template\Commands\Concerns\FinishesInitialization;
use Template\Commands\Concerns\RewritesPlaceholders;
use Template\Commands\Concerns\ResolvesChoices;
use Template\Commands\Concerns\UpdatesComposerFile;
use Template\Contracts\ChoiceContract;
use Template\Metadata;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;

/**
 * Configures this template into a real Laravel package.
 * Once everything is configured, it will delete .template/ and itself.
 */
class TemplateInitCommand extends Command
{
    use DetectsTemplateRepository;
    use FinishesInitialization;
    use ResolvesChoices;
    use RewritesPlaceholders;
    use UpdatesComposerFile;

    protected $signature = 'template:init
        {--author-name= : Author name}
        {--author-email= : Author email}
        {--package-name= : Package name, in vendor/package format}
        {--package-name-human= : Package display name}
        {--package-description= : Package description}
        {--vendor-namespace= : Vendor PHP namespace}
        {--class-name= : Main class, provider, facade, and command class name}
        {--config : Include the configuration file} {--no-config : Exclude the configuration file}
        {--routes : Include routes} {--no-routes : Exclude routes}
        {--blade : Include Blade views} {--no-blade : Exclude Blade views}
        {--translations : Include translations} {--no-translations : Exclude translations}
        {--commands : Include the example Artisan command} {--no-commands : Exclude the example Artisan command}
        {--migrations : Include migrations} {--no-migrations : Exclude migrations}
        {--facade : Include the facade} {--no-facade : Exclude the facade}
        {--assets : Include static public assets, published from the package\'s public/ folder} {--no-assets : Exclude static public assets}
        {--ai-support : Include AI support (Guidelines, Skills, etc.)} {--no-ai-support : Exclude AI support (Guidelines, Skills, etc.)}
        {--boost-skill : Include the bundled Laravel Boost skill} {--no-boost-skill : Exclude the bundled Laravel Boost skill}
        {--vue : Include the Vue, Tailwind, and Vite frontend, publishes compiled frontend assets under the same tag as --assets, mutually exclusive with --blade} {--no-vue : Exclude the Vue frontend (default)}
        {--workbench : Include the workbench dev app for running and clicking through the package standalone} {--no-workbench : Exclude the workbench dev app}
        {--dependabot : Include Dependabot} {--no-dependabot : Exclude Dependabot}
        {--issue-template : Include the GitHub issue template} {--no-issue-template : Exclude the GitHub issue template}
        {--auto-release : Include changelog automation} {--no-auto-release : Exclude changelog automation}
        {--funding : Include the GitHub FUNDING.yml} {--no-funding : Exclude the GitHub FUNDING.yml}
        {--security-policy : Include the security policy} {--no-security-policy : Exclude the security policy}';

    protected $description = 'Configure this template into a real Laravel package.';

    /** @var list<class-string<ChoiceContract>> */
    private const array PACKAGE_FEATURES = [
        ConfigChoice::class,
        RoutesChoice::class,
        TranslationsChoice::class,
        CommandsChoice::class,
        MigrationsChoice::class,
        FacadeChoice::class,
        AssetsChoice::class,
        AiSupportChoice::class,
        BoostSkillChoice::class,
        BladeFrontendChoice::class,
        VueFrontendChoice::class,
        WorkbenchChoice::class,
    ];

    /** @var list<class-string<ChoiceContract>> */
    private const array REPOSITORY_SETTINGS = [
        DependabotChoice::class,
        IssueTemplateChoice::class,
        AutoReleaseChoice::class,
        FundingChoice::class,
        SecurityPolicyChoice::class,
    ];

    /** @var list<ChoiceContract> */
    private array $choices;

    private Chisel $chisel;

    /**
     * @throws JsonException
     */
    public function handle(): int
    {
        $rootDir = rtrim(getcwd() ?: '.', '/');

        $this->chisel = Chisel::in($rootDir);

        if ($this->isTemplateRepositoryOrItsFork()) {
            $this->refuseSkeletonRepository();
            $this->initializeAiGuidelines();

            return self::SUCCESS;
        }

        $this->choices = $this->loadChoices();

        $metadata = new Metadata(
            chisel: $this->chisel,
            directoryName: basename($rootDir),
            optionResolver: fn (string $name) => $this->option($name),
        );

        if ($this->isNonInteractive()) {
            return $this->runNonInteractive($metadata);
        }

        return $this->runInteractive($metadata);
    }

    /**
     * @throws JsonException
     */
    private function runInteractive(Metadata $metadata): int
    {
        info('Configure your Laravel package');

        $metadata->collect();

        $script = $this->buildChiselScript($metadata);

        $userSelections = $script
            ->collectAnswers()
            ->onQuestion(fn (Question $question): array => multiselect($question->label, $question->options, default: $question->default ?? []))
            ->toArray();

        $conflictError = $this->frontendConflictError($this->selectedChoiceKeys($userSelections));

        if ($conflictError !== null) {
            error($conflictError);

            return self::FAILURE;
        }

        if (! confirm('Configure the package with this selection?')) {
            error('Aborted.');

            return self::FAILURE;
        }

        $this->apply(
            metadata: $metadata,
            chiselScript: $script,
            answers: $userSelections,
        );

        $manualSteps = $this->manualSteps(
            choices: $this->selectedChoiceKeys($userSelections),
        );

        if ($manualSteps !== []) {
            note(
                implode(
                    separator: "\n",
                    array: [
                        'Next steps:',
                        ...array_map(fn (string $step): string => "- {$step}", $manualSteps),
                    ]
                ),
            );
        }

        outro('Package configured successfully.');

        return self::SUCCESS;
    }

    /**
     * @throws JsonException
     */
    private function runNonInteractive(Metadata $metadata): int
    {
        $errors = $metadata->useDefaults();

        if ($errors !== []) {
            $this->printOutJson(['success' => false, 'errors' => $errors]);

            return self::FAILURE;
        }

        $script = $this->buildChiselScript($metadata);

        $selectedKeys = $this->resolveSelectedChoicesFromOptions();

        $resolved = $script
            ->collectAnswers()
            ->withAnswers([
                'package_features' => array_values(array_intersect($selectedKeys, $this->choicesKeysIn(self::PACKAGE_FEATURES))),
                'repository_settings' => array_values(array_intersect($selectedKeys, $this->choicesKeysIn(self::REPOSITORY_SETTINGS))),
            ])
            ->interactive(false)
            ->toArray();

        $selectedChoices = $this->selectedChoiceKeys($resolved);

        $conflictError = $this->frontendConflictError($selectedChoices);

        if ($conflictError !== null) {
            $this->printOutJson(['success' => false, 'errors' => [$conflictError]]);

            return self::FAILURE;
        }

        $this->apply(
            metadata: $metadata,
            chiselScript: $script,
            answers: $resolved,
        );

        $this->printOutJson([
            'success' => true,
            'errors' => [],
            'summary' => array_merge(
                $this->chisel->summary(),
                [
                    'selected_features' => $selectedChoices,
                    'manual_steps' => $this->manualSteps($selectedChoices),
                ],
            ),
        ]);

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $answers Selection answers, keyed by prompt group name.
     * @throws JsonException Propagated from decoding the existing composer.json file.
     */
    private function apply(
        Metadata $metadata,
        Script   $chiselScript,
        array    $answers,
    ): void
    {
        $this->updateReadme();

        $this->updateContributingGuide();

        $this->updateGitignore();

        $this->renameStubFiles($metadata);

        $this->updateComposerFile(
            metadata: $metadata,
            selectedChoiceKeys: $this->selectedChoiceKeys($answers),
        );

        // We delete the package's own .ai files before the initialization.
        // The choices might create a package .ai folder when AI support is selected.
        // We don't want to accidentally delete that for the end user.
        $this->chisel->file('.ai')->delete();

        // Run chisel and actually apply the changes related to the selected choices/configuration.
        $chiselScript->chisel($answers);

        $this->chisel->replacePlaceholders(
            replacements: $this->getPlaceholderReplacements($metadata),
        );

        // During the mass replacements above all mentions of "skeleton" in the selected files will be replaced with the package's slug.
        // There are occurrences where they are not placeholders.
        $this->restoreNonPlaceholderReplacements($metadata);

        $this->postInitializationCleanups();

        $this->postInitializationCommands();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function printOutJson(array $payload): void
    {
        fwrite(STDOUT, json_encode($payload, JSON_UNESCAPED_SLASHES).PHP_EOL);
    }
}
