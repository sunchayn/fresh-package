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
   - Package-author rules belong in `.template/stubs/ai-support/.ai/`, which only lands in the configured package when `ai_support` is selected.
   - Don't cross-contaminate the two.
4. Adding or changing a Choice touches several places at once, in the same commit.
   - `.template/Choices/<Name>Choice.php`, one class per selectable unit, implementing `ChoiceContract`. Never name it `*Feature`.
   - Registration on `TemplateInitCommand`'s `PACKAGE_FEATURES` or `REPOSITORY_SETTINGS` constant, whichever it configures.
   - The matching `--[choice]`/`--no-[choice]` flag pair on `TemplateInitCommand`.
   - Composer metadata and `README_PACKAGE.md`.
   - `.template/stubs/ai-support/` if the choice needs package-facing docs or skills.
   - Use `sunchayn/chisel-extended` for templating and mutations.
5. Any change to prompts or defaults must keep the non-interactive contract working: validate flag/default values before touching files, print results as single-line JSON.
6. Verify E2E changes through `template:sandbox`.
7. Keep `.gitattributes` in sync with what's dev-only.


## References
- README.md
- README_PACKAGE.md
- .template/Commands/TemplateInitCommand.php
- .template/Choices

## Examples

- To verify generated files shape, use the `template:sandbox` command with the relevant profiles or explicit flags.
- Update package-facing README content in `README_PACKAGE.md`, and package-facing contributing guidance in `.github/CONTRIBUTING_PACKAGE.md`. Keep `README.md` and `.github/CONTRIBUTING.md` focused on this skeleton itself.
- To add a selectable choice, whether a package feature or a repository setting, add a `.template/Choices/<Name>Choice.php` class. Add the skeleton files, or stub content under `.template/stubs/`. Add the matching `--[choice]` flag in `TemplateInitCommand`'s signature.
- When adding or renaming a selectable choice key, verify the derived non-interactive flag includes only the expected generated files. Run the relevant `template:sandbox` profile to prove it.
- When adding an init-only helper package, update `TemplateInitCommand`'s dependency-removal list to remove it.

## Anti-Patterns
- Running `template:init` directly against this working tree instead of through `template:sandbox`.
- Putting skeleton-maintenance rules into package-facing skills.
- Replacing placeholders with one real package name in skeleton files.
- Adding runtime dependencies for repository-maintenance convenience.
