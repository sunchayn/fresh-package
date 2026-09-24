# Package Skeleton

Opinionated Laravel package skeleton. It configures itself into a real package (backend-only or full-stack) through an interactive or flag-driven `template:init` command, then deletes its own scaffolding.

## Rules

- Keep placeholders generic and reusable. Any package identity must stay a placeholder until `template:init` replaces it.
- Keep `:author_name`, `:package_name`, `:vendor_slug`, `:package_slug` placeholders intact until `template:init` runs.
- The service provider stays a plain `Illuminate\Support\ServiceProvider`, using manual `mergeConfigFrom`/`loadRoutesFrom`/`publishes` calls.
- **Never run `template:init` against this working tree.** It's destructive and self-deletes. Use `php .template/init template:sandbox <profile>` or `--matrix` instead. That mirrors the repo into a git-ignored `.template/.sandbox/<profile>` copy.
- No runtime deps, generated files, or scaffold beyond what a package feature needs.
- When adding a new tool (ESLint, Pint, Rector, PHPStan, etc.), put its config file in `./tools`. Skip this only when the tool refuses to read its config from anywhere but the project root. TypeScript is the known example, `tsconfig.json` only works at the root.

## Configuration model

`template:init` walks the user through a set of independent Choices, then rewrites the repo to match. Each Choice is either:

- A **package feature**: config file, routes, translations, an example command, migrations, facade, publishable assets, AI support, the Laravel Boost skill, Blade frontend, Vue frontend.
- A **repository setting**: Dependabot, GitHub issue template, auto-release automation, GitHub FUNDING.yml, security policy.

Selecting a Choice wires the matching source into `composer.json` and the service provider; declining removes any reference to it. Package features the skeleton's own boot path or test suite depends on (config, routes, translations, commands, migrations, facade, assets, blade) keep their source at the repo root and get deleted on decline. Everything else (`ai_support`, `boost_skill`, `vue`, and every repository setting) keeps its source only under `.template/stubs/`, opt-in: nothing lands on the tree until the matching Choice is selected. Blade and Vue frontends are mutually exclusive.

Once every Choice is resolved, placeholders are replaced with real package identity, `README.md` is generated from `.template/stubs/repository_settings/README_PACKAGE.md`, and `.template/` deletes itself along with the `template:init` command. What remains is an ordinary Laravel package with no trace of the skeleton machinery.

## How `template:init` works

- Lives entirely in `.template/`, never `src/`, so it never appears in the configured package's command surface.
- Runs on `composer install`/`update` (`post-install-cmd`/`post-update-cmd` calls `@php .template/init`), or manually via `composer install --no-scripts && php .template/init`.
- Interactive mode prompts for package identity and Choices with Laravel Prompts. Non-interactive mode reads `--author-name`, `--package-name`, `--config`/`--no-config`, and the equivalent flag pair for every other Choice, then prints a JSON result.
- Applying the selection updates `README.md` and `CONTRIBUTING.md`, renames stub files, updates `composer.json`, runs each Choice's `onSelect`/`onDecline`, replaces placeholders, then runs any post-init cleanup and commands.
- `Metadata.php` collects package identity (name, vendor, namespace, class name, author).

## Adding/changing a Choice

Each selectable unit lives in `.template/Choices/PackageFeature/<Name>Choice.php` or `.template/Choices/RepositorySettings/<Name>Choice.php`, extending `Template\Choices\AbstractChoice`.

Keep these in sync when you add or change one:
- The `--[choice]`/`--no-[choice]` flag pair in `TemplateInitCommand`'s signature, and its entry in `PACKAGE_FEATURES` or `REPOSITORY_SETTINGS`.
- Composer metadata and `.template/stubs/repository_settings/README_PACKAGE.md`.
- `dependsOn()` when a Choice's `onSelect`/`onDecline` needs another Choice's output or absence first. `AiSupportChoice` is a common dependency since it seeds `.ai/`.

## Chisel API

`Laravel\Chisel\Chisel` (from `sunchayn/chisel-extended`). No repo-local wrapper, no Operations layer.

- On `$chisel` directly: `renamePath()`, `copyDirectory()`, `replacePlaceholders()`, `runCommand()`, `php()`, `npm()`.
- `$chisel->file(...)` and `files(...)` return a handle: `delete()` (also removes dirs), `replace()`, `removeLinesContaining()`, `removeSection()`, `insertAfter()`, `removeMarkdownSection()`.

## AGENT BEHAVIOR

### Communication Rules

- **No Emojis** in responses (except requirement markers).
- **No Fluff Documentation**: Never generate unnecessary markdown files (e.g., `COMPLETION_REPORT.md`, `SUMMARY.md`, etc.).
- **Never assume action from observation**.
- When the user asks a question, you MUST NOT imply intent and start performing work or editing files without answering the question first. A question is NOT a command to do something. You must always answer the question as-is and wait for explicit instruction to proceed with any modifications.
- When reporting information to me, be extremely concise and sacrifice grammar for the sake of concision.

### Artifact Management Rules

- **Use `.ai/artifacts/` folder EXCLUSIVELY** for all AI-generated artifacts including:
    - Execution plans (unless you are in an environment that has interactive plans like Antigravity)
    - Summaries
    - Audits
    - Analysis reports
    - Investigation notes
    - Any other AI-generated documentation
- **NEVER write AI-generated artifacts** to other folders (e.g., `docs/`, root directory).
- **Keep artifacts organized** with descriptive filenames including dates (e.g., `SECURITY_AUDIT_2026_01_14.md`).
- **NEVER modify user-managed documentation** in `docs/`, `wiki/`, or any other project folders unless explicitly requested.

## Commands

- Full validation: `composer test`
- Formatting check: `composer style:check`
- Static analysis: `composer analyse`
- Tests, in parallel: `composer test:parallel`
- Switch Laravel version: `composer switch:l12` (or `l13`)
- Verify the skeleton: `php .template/init template:sandbox --matrix`

## Skills

- `skeleton-development`: changing this skeleton repo itself.
- Package-facing skills (`scaffold-module`, `create-dto-action`, `create-query`, `write-php-code`, `write-comments`, `write-php-test`, `task-finalization`, `package-compatibility`) live in `.template/stubs/ai_support_choice/.ai/skills/`, not here. `package-release` lives in `.template/stubs/repository_settings/.ai/skills/`, and `package-generate-skill` in `.template/stubs/boost_skill_choice/.ai/skills/`; both only land in the configured package's `.ai/skills/` when their own choice is selected too. Read them from those paths.
