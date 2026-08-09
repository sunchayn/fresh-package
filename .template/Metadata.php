<?php

declare(strict_types=1);

namespace Template;

use Closure;
use Laravel\Chisel\Chisel;
use function Laravel\Prompts\text;

/**
 * Collects package identity (name, namespace, class name, author) either
 * interactively (Laravel Prompts) or from flags/defaults for non-interactive runs.
 *
 * @source https://github.com/laravel/package-skeleton/blob/main/configure.php
 */
class Metadata
{
    /** @var array<string, string> */
    private array $data = [];

    /**
     * @param  Closure(string): mixed  $optionResolver  Resolves a `--foo-bar` CLI option by its dashed name.
     */
    public function __construct(
        private readonly Chisel $chisel,
        private readonly string $directoryName,
        private Closure $optionResolver,
    ) {
        //
    }

    public function collect(): void
    {
        foreach ($this->fields() as $key => $field) {
            $this->data[$key] = text(
                $field['label'],
                default: $field['default'](),
                required: true,
                validate: $field['validate'] ?? null,
                hint: $field['hint'],
            );
        }
    }

    public function packageName(): string
    {
        return $this->data['package_name'];
    }

    public function packageNameHuman(): string
    {
        return $this->data['package_name_human'];
    }

    public function packageDescription(): string
    {
        return $this->data['package_description'];
    }

    public function vendorSlug(): string
    {
        return explode('/', $this->data['package_name'])[0];
    }

    public function packageSlug(): string
    {
        return explode('/', $this->data['package_name'])[1];
    }

    public function authorName(): string
    {
        return $this->data['author_name'];
    }

    public function authorEmail(): string
    {
        return $this->data['author_email'];
    }

    public function vendorNamespace(): string
    {
        return $this->data['vendor_namespace'];
    }

    public function className(): string
    {
        return $this->data['class_name'] ?? '';
    }

    public function providerPath(): string
    {
        return sprintf('src/%sServiceProvider.php', $this->className());
    }

    /**
     * @return array<string, array{label: string, hint: string, default: callable, validate?: callable}>
     */
    public function fields(): array
    {
        return [
            'author_name' => [
                'label' => 'Author name',
                'hint' => 'Used in composer.json credits and README attribution.',
                'default' => fn (): mixed => $this->option('author-name') ?? $this->gitConfig('user.name') ?: 'Vendor Name',
            ],
            'author_email' => [
                'label' => 'Author email',
                'hint' => 'Used in composer.json package author metadata.',
                'default' => fn (): mixed => $this->option('author-email') ?? $this->gitConfig('user.email') ?: 'author@example.com',
                'validate' => function ($value): ?string {
                    if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                        return 'Must be a valid email address.';
                    }

                    return null;
                },
            ],
            'package_name' => [
                'label' => 'Package name',
                'hint' => 'Used in composer.json and as the package name in Packagist.',
                'default' => fn (): mixed => $this->option('package-name') ?? implode('/', [
                    $this->slug($this->authorName()) ?: 'vendor-name',
                    $this->slug($this->directoryName === 'package-skeleton' ? 'my-package' : $this->directoryName),
                ]),
                'validate' => function ($value): ?string {
                    if (! preg_match('/^[a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9](([_.]|-{1,2})?[a-z0-9]+)*$/', $value)) {
                        return 'Package name must be in the format vendor/package.';
                    }

                    return null;
                },
            ],
            'package_name_human' => [
                'label' => 'Package display name',
                'hint' => 'Used as the readable package name in README.',
                'default' => fn (): mixed => $this->option('package-name-human') ?? $this->headline(str_replace('-', ' ', $this->packageSlug())),
            ],
            'package_description' => [
                'label' => 'Package description',
                'hint' => 'Used in composer.json and README intro copy.',
                'default' => fn (): mixed => $this->option('package-description') ?? '',
            ],
            'vendor_namespace' => [
                'label' => 'Vendor namespace',
                'hint' => 'Used as the top-level PHP namespace, for example VendorName\\PackageName.',
                'default' => fn (): mixed => $this->option('vendor-namespace') ?? $this->studly($this->slug($this->packageNameHuman())),
                'validate' => function ($value): ?string {
                    if (preg_match('/^[A-Z_a-z][A-Z_a-z0-9]*$/', $value) !== 1) {
                        return 'Vendor namespace must be a valid PHP namespace.';
                    }

                    return null;
                },
            ],
            'class_name' => [
                'label' => 'Main class name',
                'hint' => 'Used for the main class, service provider, facade, and command class names.',
                'default' => fn (): mixed => $this->option('class-name') ?? $this->studly($this->slug($this->packageNameHuman())),
                'validate' => function ($value): ?string {
                    if (preg_match('/^[A-Z_a-z][A-Z_a-z0-9]*$/', $value) !== 1) {
                        return 'Class name must be a valid PHP class name.';
                    }

                    return null;
                },
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function useDefaults(): array
    {
        $errors = [];

        foreach ($this->fields() as $key => $field) {
            $value = $field['default']();
            $error = isset($field['validate']) ? $field['validate']($value) : null;

            if ($error !== null) {
                $errors[] = sprintf('%s: %s', $field['label'], $error);

                if ($key === 'package_name') {
                    break;
                }

                continue;
            }

            $this->data[$key] = $value;
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    private function option(string $name): mixed
    {
        return ($this->optionResolver)($name);
    }

    private function gitConfig(string $key): string
    {
        $result = $this->chisel->runCommand(['git', 'config', $key]);
        $output = $result['output'];

        // The output holds an error message on failure, when there is no git or the directory is not a git repository, so it must not be trusted without checking success.
        return $result['success'] && is_string($output) ? $output : '';
    }

    private function slug(string $value): string
    {
        return trim(strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '-', $value)), '-');
    }

    private function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }

    private function headline(string $value): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $value));
    }
}
