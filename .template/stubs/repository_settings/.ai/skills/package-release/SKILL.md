---
name: package-release
description: "Use this skill to prepare Laravel package releases. Covers CHANGELOG.md, release-please configuration, commit message conventions, version checks, tags, and release workflow changes. Never publish autonomously."
license: MIT
metadata:
  author: laravel
---

# Package Release

## Primary Goal

Prepare a safe package release without tagging, pushing, or publishing unless the user explicitly approves that action.

## Workflow

1. Review `CHANGELOG.md`, `release-please-config.json`, `.release-please-manifest.json`, `.github/workflows/release.yml`, and pending package changes.
2. Validate the release state with `composer test` before recommending a release.
3. Confirm recent commit messages follow the Conventional Commits format. `release-please` reads them to decide the next version and changelog section. Commits that do not follow the format are skipped.
4. Review `.github/workflows/php-tests.yml`'s supported matrix before changing release workflow behavior.
5. Do not tag, push, merge a release pull request, or publish without explicit user approval.

## Examples

- To prepare a release, check recent commit messages against the Conventional Commits format. Run `composer test`. Confirm the open release pull request's version bump looks right.
- Add a new `changelog-sections` entry to `release-please-config.json` when a new commit type (for example `docs`) should get its own changelog section.

## Anti-Patterns

- Creating tags, pushing branches, merging a release pull request, or publishing releases without explicit approval.
- Skipping `composer test` before a release recommendation.
- Editing `CHANGELOG.md` by hand instead of through a conventional commit that `release-please` picks up.
- Changing release workflow behavior without checking the supported matrix in `.github/workflows/php-tests.yml`.
