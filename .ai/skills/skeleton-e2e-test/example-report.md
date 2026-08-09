# E2E test report, 2026-09-13

Status: PASSED. All profiles pass under both supported Laravel versions, by automated check and by static verification.

## Freshness check
- Choices found in `.template/Choices/`: config, routes, translations, commands, migrations, facade, assets, ai_support, boost_skill, blade, vue, dependabot, issue_template, auto_release, funding, security_policy.
- Rows found in `choice-intent-map.md`: same 16 keys.
- Result: IN SYNC.

## Setup
- Worktrees: `../fresh-package-l12`, `../fresh-package-l13`, from commit a515035.
- Laravel versions under test: 12, 13, from `switch:l12`/`switch:l13` in composer.json.
- PHP version: 8.4.25.

## Profiles run
- `backend-only` (registered): flags `--no-vue`.
- `with-frontend` (registered): flags `--vue --no-blade`.
- `partial` (registered): flags `--config --routes --facade --migrations --auto-release --security-policy --no-blade --no-translations --no-assets --no-ai-support --no-boost-skill --no-vue --no-dependabot --no-issue-template --no-funding --no-commands`.
- `all-in` (registered): every choice on, Blade not Vue.
- `all-out` (registered): every choice off.
- `dependabot-on` / `dependabot-off` (custom, created for this run): `--dependabot` / `--no-dependabot`.
- `issue-template-on` / `issue-template-off` (custom, created for this run): `--issue-template` / `--no-issue-template`.
- `auto-release-on` / `auto-release-off` (custom, created for this run): `--auto-release` / `--no-auto-release`.
- `funding-on` / `funding-off` (custom, created for this run): `--funding` / `--no-funding`.
- `security-policy-on` / `security-policy-off` (custom, created for this run): `--security-policy` / `--no-security-policy`.
- `ai-support-only` (custom, created for this run): `--ai-support --no-boost-skill`.
- `boost-without-ai` (custom, created for this run): `--no-ai-support --boost-skill`.
- `conflict` (custom, created for this run, expected to fail): `--blade --vue`.

## Matrix results
- L12 / `backend-only`: PASS.
- L12 / `with-frontend`: PASS.
- L12 / `partial`: PASS.
- L12 / `all-in`: PASS.
- L12 / `all-out`: PASS.
- L12 / `dependabot-on`: PASS.
- L12 / `dependabot-off`: PASS.
- L12 / `conflict`: PASS (failed template:init as expected, no mutation).
- L13 / same 13 profiles: PASS, same outcomes as L12.

## Static verification log
- [all-in] `.template/` gone: confirmed absent. OK
- [all-in] placeholder grep for `:author_name`, `:package_name`, `:vendor_slug`, `:package_slug`: none found. OK
- [all-in] `config` selected, `config/all-in.php` exists: found. OK
- [all-in] `facade` selected, composer.json extra.laravel.aliases set: found, points at `AllIn\AllIn\Facades\AllIn`. OK
- [all-in] `ai_support` selected, composer.json post-update-cmd includes `npx agenteq sync --yes`: found. OK
- [all-in] `blade` selected, `resources/views/placeholder.blade.php` exists: found. OK
- [all-in] judgement check, provider's views section only registered once, no duplicate `loadViewsFrom` call from Blade and Vue both firing: confirmed single call. OK
- [all-out] every package feature and repository setting declined, matching directory list empty: confirmed for all 16 keys. OK
- [all-out] README has no leftover "Publishing the..." sections for any declined feature: confirmed. OK
- [with-frontend] `vue` selected, `blade` declined, provider's views section present exactly once: found, not duplicated. OK
- [with-frontend] `Route::view('with-frontend', 'with-frontend::app')` resolves, `view()->exists()` true under both Laravel versions: confirmed via phpstan pass, no argument.type error. OK
- [dependabot-off] `.github/dependabot.yml` absent, README has no "Dependabot" mention: confirmed. OK
- [ai-support-only] `boost_skill` declined while `ai_support` selected, `.ai/` still present but `resources/boost/skills/` absent: confirmed. OK
- [boost-without-ai] judgement check, `ai_support` declined then `boost_skill` selected still completes without a crash, matches the documented edge case in the intent map rather than a real bug: confirmed, no exception. OK

## Findings
None.

## Worktree state
Clean aside from `vendor/` and `composer.lock`.

## Open question
Remove `../fresh-package-l12` and `../fresh-package-l13` now, yes or no.
