# Choice intent map

This is the source of truth the `skeleton-e2e-test` skill checks itself against before running anything. Every row here must correspond to exactly one `key()` in `.template/Choices/PackageFeature/*.php` or `.template/Choices/RepositorySettings/*.php`, and every such `key()` must have a row here. If they drift apart, the skill stops instead of testing against a stale map.

Last verified against the codebase: 2026-09-14.

## Universal, on every successful `template:init` run, regardless of choices
- `.template/` is gone, along with the `template:init` and `template:sandbox` commands.
- `.template/stubs/repository_settings/README_PACKAGE.md` and `.template/stubs/repository_settings/.github/CONTRIBUTING_PACKAGE.md` are gone (along with the rest of `.template/`), `README.md` and `CONTRIBUTING.md` are generated in their place.
- No literal `:author_name`, `:package_name`, `:vendor_slug`, or `:package_slug` remains anywhere.
- `composer.json` no longer lists `@php .template/init` under `post-install-cmd` or `post-update-cmd`, and drops the hook entirely if that was its only command.
- If `ai_support` is selected, `composer.json` gains `npx agenteq sync --yes` under `post-update-cmd`.
- If `facade` is selected, `composer.json`'s `extra.laravel.aliases` is set, otherwise that key is absent entirely.

## Per choice, package features
Selected means present, declined means absent, unless noted.

| Key | Present when selected | Removed when declined |
|---|---|---|
| `config` | `config/<slug>.php` | `config/`, README "Publishing the Configuration File" section, provider config section, test config section, the `../../config` line in `tools/phpstan/phpstan.neon.dist` |
| `routes` | `routes/web.php`, `routes/api.php` | `routes/`, provider routes section, README route mentions, the `../../routes` line in phpstan config |
| `translations` | `lang/` | `lang/`, README "Publishing the Translations" section, provider and test translations sections |
| `commands` | `src/Console/Commands/` | that directory, the command import and section in the provider, the test command section, README command mentions |
| `migrations` | `database/migrations/` | that directory, README "Publishing and Running the Migrations" section, provider migrations section, the `../../database` line in phpstan config |
| `facade` | `src/Facades/`, `composer.json` extra.laravel.aliases | `src/Facades/`, README facade mentions, the aliases key |
| `assets` | `public/` | `public/`, README "Publishing the Public Assets" section, unless `vue` is selected, that section only disappears once both `assets` and `vue` are declined |
| `ai_support` | `.ai/` (GUIDELINES.md, skills) | `.ai/` entirely, always deleted first regardless of selection, then recreated only if selected |
| `boost_skill` | `resources/boost/skills/<slug>-development/` | that directory, `.ai/skills/package-generate-skill` if `ai_support` is also present, "Boost"/"boost" mentions in README and GUIDELINES |
| `blade` | `resources/views/placeholder.blade.php` | that file, the test views section, `.ai/skills/scaffold-module/SKILL.md` no longer mentions `resources/views` |
| `vue` | the vue_choice stub tree (`resources/js/`, `resources/css/`, `package.json`, `vite.config.js`, `tsconfig.json`), a `publishes()` call for `resources/dist` in the provider, a `Route::view` line in `routes/web.php` | none of the above, and no `resources/js` mention left in `.ai/GUIDELINES.md` or the scaffold-module skill if `ai_support` is present |

`blade` and `vue` share the provider's views section (the `loadViewsFrom()` call and its `publishes()` block) and the README "Publishing the Views" section, since Vue's own view lives in the same `resources/views` directory Blade's placeholder used. Both only disappear once neither `blade` nor `vue` is selected. `blade` and `vue` are also mutually exclusive, selecting both must fail `template:init` outright with no mutation to the tree at all, not just conflicting output.

## Per choice, repository settings
| Key | Present when selected | Removed when declined |
|---|---|---|
| `auto_release` | `CHANGELOG.md`, `release-please-config.json`, `.release-please-manifest.json`, `.github/workflows/release.yml`, `.ai/skills/package-release` if `ai_support` is present | all of the above, README "Changelog" section and changelog mentions, GUIDELINES `package-release` mention |
| `dependabot` | `.github/dependabot.yml`, with its npm ecosystem entry pointed at `/` if `vue` is also selected, or removed entirely if `vue` is declined | that file, README Dependabot mentions |
| `funding` | `.github/FUNDING.yml` | that file |
| `issue_template` | `.github/ISSUE_TEMPLATE/` | that directory |
| `security_policy` | `.github/SECURITY.md` | that file, README "Security Vulnerabilities" section |
