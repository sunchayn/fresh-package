<?php

declare(strict_types=1);

namespace Template\Commands\Concerns;

use Laravel\Chisel\Chisel;
use Laravel\Chisel\Filesystem\PendingFiles;
use Laravel\Chisel\Question;
use Laravel\Chisel\Script;
use LogicException;
use Template\Choices\PackageFeature\AssetsChoice;
use Template\Choices\PackageFeature\BladeFrontendChoice;
use Template\Choices\PackageFeature\CommandsChoice;
use Template\Choices\PackageFeature\ConfigChoice;
use Template\Choices\PackageFeature\FacadeChoice;
use Template\Choices\PackageFeature\MigrationsChoice;
use Template\Choices\PackageFeature\TranslationsChoice;
use Template\Choices\PackageFeature\VueFrontendChoice;
use Template\Choices\RepositorySettings\DependabotChoice;
use Template\Commands\TemplateInitCommand;
use Template\Contracts\ChoiceContract;
use Template\Metadata;

/**
 * @mixin TemplateInitCommand
 */
trait ResolvesChoices
{
    private function buildChiselScript(Metadata $metadata): Script
    {
        $script = Chisel::script($this->chisel->rootDir())->safe((bool) $this->option('safe'));

        $script->questions([
            Question::multiselect(
                name: 'package_features',
                label: 'Package Features',
                options: $this->optionsFor(self::PACKAGE_FEATURES),
                default: $this->defaultSelectedKeysFor(self::PACKAGE_FEATURES),
            ),
            Question::multiselect(
                name: 'repository_settings',
                label: 'Repository Settings',
                options: $this->optionsFor(self::REPOSITORY_SETTINGS),
                default: $this->defaultSelectedKeysFor(self::REPOSITORY_SETTINGS),
            ),
        ]);

        foreach ($this->choices as $choice) {
            $script->selected(
                key: in_array($choice::class, self::PACKAGE_FEATURES, true) ? 'package_features' : 'repository_settings',
                value: $choice::key(),
                then: fn (Chisel $chisel) => $choice->onSelect($chisel, $metadata),
                else: fn (Chisel $chisel) => $choice->onDecline($chisel, $metadata),
            );
        }

        // todo refactor the below operations out of this class. Make they more self-contained somehow.

        $script->selectedAny(
            key: 'package_features',
            values: [AssetsChoice::key(), VueFrontendChoice::key()],
            else: function (Chisel $chisel): void {
                // Both of AssetsChoice and VueFrontendChoice publishes assets (static or compiled) under the same tag.
                // The README section documenting that tag only goes away once neither is selected.
                $chisel->file('README.md')->removeMarkdownSection('Publishing the Public Assets');
            },
        );

        // BladeFrontendChoice and VueFrontendChoice both need the provider's views registration,
        // since Vue's own view lives in the same resources/views directory Blade's placeholder used.
        // That registration, and the README section documenting it, only go away once neither is selected.
        $script->selectedAny(
            key: 'package_features',
            values: [BladeFrontendChoice::key(), VueFrontendChoice::key()],
            else: function (Chisel $chisel) use ($metadata): void {
                $chisel->file('README.md')->removeMarkdownSection('Publishing the Views');

                $chisel->file('resources/views')->delete();

                $chisel->file($metadata->providerPath())->removeSection('views');
            },
        );

        // The README intro about publishing has nothing to point at once every publishable resource is declined.
        $script->selectedAny(
            key: 'package_features',
            values: [
                ConfigChoice::key(),
                TranslationsChoice::key(),
                AssetsChoice::key(),
                MigrationsChoice::key(),
                BladeFrontendChoice::key(),
                VueFrontendChoice::key(),
            ],
            then: fn (Chisel $chisel) => $chisel->file('README.md')->removeSectionMarkers('publish-intro'),
            else: fn (Chisel $chisel) => $chisel->file('README.md')->removeSection('publish-intro'),
        );

        // The any-features section stays when at least one of these choices is selected, and goes otherwise.
        $script->selectedAny(
            key: 'package_features',
            values: [
                ConfigChoice::key(),
                TranslationsChoice::key(),
                AssetsChoice::key(),
                MigrationsChoice::key(),
                CommandsChoice::key(),
                BladeFrontendChoice::key(),
                VueFrontendChoice::key(),
            ],
            then: fn (Chisel $chisel) => $chisel->file($metadata->providerPath())->removeSectionMarkers('any-features'),
            else: function (Chisel $chisel) use ($metadata): void {
                $chisel->file($metadata->providerPath())->removeSection('any-features');
            },
        );

        // The npm ecosystem entry only makes sense once Vue is selected,
        // since that's the only Choice that ships a package.json, and it lands at the package root.
        $script->apply(function (Chisel $chisel, array $answers): void {
            if (! in_array(DependabotChoice::key(), (array) ($answers['repository_settings'] ?? []), true)) {
                return;
            }

            if (in_array(VueFrontendChoice::key(), (array) ($answers['package_features'] ?? []), true)) {
                $chisel->file('.github/dependabot.yml')->replace(
                    search: 'directory: "/.template/stubs/vue_choice"',
                    replace: 'directory: "/"',
                );

                return;
            }

            $chisel->file('.github/dependabot.yml')->replace(
                search: <<<'YAML'


                      - package-ecosystem: "npm"
                        directory: "/.template/stubs/vue_choice"
                        versioning-strategy: increase
                        schedule:
                          interval: "weekly"
                        labels:
                          - "dependencies"
                        groups:
                          npm:
                            patterns:
                              - "*"

                    YAML,
                replace: "\n",
            );
        });

        return $script;
    }

    /**
     * @param  list<class-string<ChoiceContract>>  $classes Classes to filter by.
     * @return list<ChoiceContract> Choices matching one of the given classes.
     */
    private function choicesIn(array $classes): array
    {
        return array_values(array_filter(
            $this->choices,
            fn (ChoiceContract $choice): bool => in_array($choice::class, $classes, true),
        ));
    }

    /**
     * @param  list<class-string<ChoiceContract>>  $classes Classes to filter by.
     * @return list<string> Keys of choices matching one of the given classes.
     */
    private function choicesKeysIn(array $classes): array
    {
        return array_map(fn (ChoiceContract $choice): string => $choice::key(), $this->choicesIn($classes));
    }

    /**
     * @param  list<class-string<ChoiceContract>>  $classes Classes to build options for.
     * @return array<string, string> Choice keys mapped to their labels.
     */
    private function optionsFor(array $classes): array
    {
        $choices = $this->choicesIn($classes);

        return array_combine(
            keys: array_map(fn (ChoiceContract $choice): string => $choice::key(), $choices),
            values: array_map(fn (ChoiceContract $choice): string => $choice->label(), $choices),
        );
    }

    /**
     * @param  list<class-string<ChoiceContract>>  $classes Classes to compute defaults for.
     * @return list<string> Default-selected keys.
     */
    private function defaultSelectedKeysFor(array $classes): array
    {
        return array_values(
            array_diff(
                $this->choicesKeysIn($classes),
                [
                    // Blade is the default frontend, and it is mutually exclusive with Vue.
                    VueFrontendChoice::key(),
                    // This skeleton favors dependency injection over facades by default.
                    FacadeChoice::key(),
                ],
            )
        );
    }

    /**
     * @return list<string>
     */
    private function choicesKeys(): array
    {
        return array_map(fn (ChoiceContract $choice): string => $choice::key(), $this->choices);
    }

    /**
     * @return list<string>
     */
    private function defaultSelectedChoiceKeys(): array
    {
        return $this->defaultSelectedKeysFor(
            classes: array_merge(self::PACKAGE_FEATURES, self::REPOSITORY_SETTINGS),
        );
    }

    /**
     * Merges the two prompt groups' answers back into one flat list of selected choice keys.
     *
     * @param  array<string, mixed>  $answers Answers keyed by their prompt group name.
     * @return list<string> Keys from both groups, flattened into one list.
     */
    private function selectedChoiceKeys(array $answers): array
    {
        return array_values(
            array_map(
                strval(...),
                [
                    ...($answers['package_features'] ?? []),
                    ...($answers['repository_settings'] ?? []),
                ],
            ),
        );
    }

    /**
     * Resolves each key's selection from its --x and --no-x flag pair.
     *
     * @return list<string> Keys selected via their derived option flags.
     */
    private function resolveSelectedChoicesFromOptions(): array
    {
        $selected = [];

        foreach ($this->choicesKeys() as $key) {
            $option = str_replace('_', '-', $key);

            if ($this->option($option)) {
                $selected[] = $key;

                continue;
            }

            if ($this->option('no-'.$option)) {
                continue;
            }

            if (in_array($key, $this->defaultSelectedChoiceKeys(), true)) {
                $selected[] = $key;
            }
        }

        return $selected;
    }

    /**
     * @return list<ChoiceContract>
     */
    private function loadChoices(): array
    {
        return array_map(
            fn (string $choice): ChoiceContract => new $choice,
            $this->sortByDependencies([...self::PACKAGE_FEATURES, ...self::REPOSITORY_SETTINGS]),
        );
    }

    /**
     * Topologically sorts choice classes so each one runs after every choice it depends on.
     * Choices with no dependency between them keep their original relative order.
     *
     * @param  list<class-string<ChoiceContract>>  $classes Classes to sort, in declaration order.
     * @return list<class-string<ChoiceContract>> The same classes, dependency-first.
     */
    private function sortByDependencies(array $classes): array
    {
        $sorted = [];
        $visiting = [];

        $visit = function (string $class) use (&$visit, &$sorted, &$visiting, $classes): void {
            if (in_array($class, $sorted, true)) {
                return;
            }

            if (isset($visiting[$class])) {
                throw new LogicException("Circular choice dependency detected involving {$class}.");
            }

            $visiting[$class] = true;

            // @phpstan-ignore method.notFound (PHPStan can't see $class is a class-string<ChoiceContract> here)
            foreach ((new $class)->dependsOn() as $dependency) {
                if (in_array($dependency, $classes, true)) {
                    $visit($dependency);
                }
            }

            unset($visiting[$class]);
            $sorted[] = $class;
        };

        foreach ($classes as $class) {
            $visit($class);
        }

        // @phpstan-ignore return.type (same as above, $sorted holds class-string<ChoiceContract> values)
        return $sorted;
    }

    /**
     * @param  list<string>  $selectedChoiceKeys Keys chosen by the user, or resolved from flags.
     */
    private function frontendConflictError(array $selectedChoiceKeys): ?string
    {
        $bothSelected = in_array(VueFrontendChoice::key(), $selectedChoiceKeys, true)
            && in_array(BladeFrontendChoice::key(), $selectedChoiceKeys, true);

        return $bothSelected
            ? 'Choose either the Vue frontend or Blade views, not both.'
            : null;
    }
}
