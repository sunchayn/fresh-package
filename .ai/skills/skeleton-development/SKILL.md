---
name: skeleton-development
description: "Use this skill when changing this Laravel package skeleton repository itself: placeholders, configure flow, defaults, AI guidance, sandbox testing, dist hygiene. Do not use for ordinary package feature work after a package has been configured."
license: MIT
metadata:
  author: sunchayn
---

# Skeleton Development

## Primary Goal

Evolve the package skeleton without making it less useful for future package authors.

## Guidelines

Use these to decide how a change should land, grouped by the decision it affects.

1. Never run `template:init` on this working tree. It self-deletes and is destructive. Test through `php .template/init template:sandbox <profile>` (or `--matrix`) instead.
2. Placeholders stay literal until init runs. `:author_name`, `:package_name`, `:vendor_slug`, `:package_slug` must not be replaced with a real value anywhere in skeleton files.
3. Split AI guidance by audience.
   - Skeleton-maintenance rules belong in this repo's root `.ai/`/`CLAUDE.md` and this skill.
   - Package-author rules belong in `.template/stubs/ai_support_choice/.ai/`, which only lands in the configured package when `ai_support` is selected.
   - Don't cross-contaminate the two.
4. Adding or changing a Choice touches several places at once, in the same commit.
   - `.template/Choices/<Name>Choice.php`, one class per selectable unit, implementing `ChoiceContract`. Never name it `*Feature`.
   - Registration on `TemplateInitCommand`'s `PACKAGE_FEATURES` or `REPOSITORY_SETTINGS` constant, whichever it configures.
   - The matching `--[choice]`/`--no-[choice]` flag pair on `TemplateInitCommand`.
   - Composer metadata and `.template/stubs/repository_settings/README_PACKAGE.md`.
   - `.template/stubs/ai_support_choice/` if the choice needs package-facing docs or skills.
   - Use `sunchayn/chisel-extended` for templating and mutations.
   - Repository settings (Dependabot, issue template, auto release, funding, security policy) and the `*_PACKAGE` files are opt-in: their source lives only under `.template/stubs/repository_settings/`, mirroring its root-relative destination path, and each choice's `onSelect` promotes its own file(s) with `renamePath()`. Package features that the skeleton's own boot path or test suite depends on (config, routes, translations, commands, migrations, facade, assets, blade) stay root-always/delete-on-decline instead, since `SkeletonServiceProvider` requires them to exist for `composer test`/`analyse`/`serve` to work on this repo itself. `ai_support`, `vue`, and `boost_skill` are opt-in stubs (`.template/stubs/{key}_choice/`) too, since nothing at boot depends on them.
5. Any change to prompts or defaults must keep the non-interactive contract working: validate flag/default values before touching files, print results as single-line JSON.
6. Verify E2E changes through `template:sandbox`.
7. Keep `.gitattributes` in sync with what's dev-only.


## References
- README.md
- .template/stubs/repository_settings/README_PACKAGE.md
- .template/Commands/TemplateInitCommand.php
- .template/Choices

## Examples

- To verify generated files shape, use the `template:sandbox` command with the relevant profiles or explicit flags.
- Update package-facing README content in `.template/stubs/repository_settings/README_PACKAGE.md`, and package-facing contributing guidance in `.template/stubs/repository_settings/.github/CONTRIBUTING_PACKAGE.md`. Keep `README.md` and `.github/CONTRIBUTING.md` focused on this skeleton itself.
- To add a selectable choice, whether a package feature or a repository setting, add a `.template/Choices/<Name>Choice.php` class. Add the skeleton files, or stub content under `.template/stubs/`. Add the matching `--[choice]` flag in `TemplateInitCommand`'s signature.
- When adding or renaming a selectable choice key, verify the derived non-interactive flag includes only the expected generated files. Run the relevant `template:sandbox` profile to prove it.
- When adding an init-only helper package, update `TemplateInitCommand`'s dependency-removal list to remove it.

## Anti-Patterns
- Running `template:init` directly against this working tree instead of through `template:sandbox`.
- Putting skeleton-maintenance rules into package-facing skills.
- Replacing placeholders with one real package name in skeleton files.
- Adding runtime dependencies for repository-maintenance convenience.
