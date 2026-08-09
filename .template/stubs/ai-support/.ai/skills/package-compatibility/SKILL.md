---
name: package-compatibility
description: "Use this skill for reviewing Laravel package compatibility across composer constraints, PHP versions, Laravel versions, Testbench versions, or matrix-sensitive code and workflow changes."
license: MIT
metadata:
  author: laravel
---

# Package Compatibility

## Primary Goal

Keep package code, dependencies, and workflows compatible with the supported PHP/Laravel matrix declared in `composer.json` and exercised by `.github/workflows/php-tests.yml`.

## Workflow

1. Read `composer.json` first. Check the PHP, Laravel, and Testbench constraints. Check the `switch`/`switch:lNN` scripts that pin each Laravel major's Testbench, PHPUnit, and Larastan versions.
2. Check changed code against the oldest and newest supported Laravel APIs and PHP syntax before adopting newer framework or language features.
3. Review `.github/workflows/php-tests.yml`'s PHP-by-Laravel matrix and its `exclude` list. Treat that matrix, not this skill, as the source of truth for supported combinations. Coverage runs only on the single latest PHP and latest Laravel cell.
4. When changing dependencies, confirm the constraints still allow every Laravel major in the matrix. Use `composer switch:lNN` locally to reproduce a specific cell.
5. Validate with the smallest local command available. Run `composer switch:lNN && composer test`. Rely on CI for the full matrix.

## Examples

- Before merging a new Laravel API call into shared package code, check that the API exists across every Laravel major in the `php-tests.yml` matrix.
- To review a dependency bump, check the Composer constraints and the Testbench constraints across every `switch:lNN` script.

## Anti-Patterns

- Assuming the latest local dependency version represents the whole support matrix.
- Adding PHP syntax or Laravel APIs that exceed `composer.json` constraints.
- Removing matrix cells or the `switch:lNN` scripts because they are slower than a single happy path.
