---
name: task-finalization

description: "Use this skill before marking any package change complete. Runs the quality tools this package actually has, scoped to what changed, and sets the review step for work done by a subagent."

license: MIT

metadata:
  author: sunchayn

---

# Task Finalization

## Primary Goal

Never call a change done until the tools that would catch a regression have actually run against it.

## Workflow

1. Check which files changed (`git status`, `git diff --name-only`).
2. If a PHP file under `src/`, `tests/`, `config/`, `database/`, or `workbench/` changed, run in order: `composer style:fix`, `composer analyse`, `composer rector:check` (or `composer rector` to apply), then `composer test:parallel` filtered to the affected area while iterating, and `composer test` before finishing.
3. If `resources/js` changed (frontend feature kept), run `npm run style:check`, `npm run type:check`, and `npm run test`.
4. If a Choice, template stub, or anything under `.template/` changed, that is skeleton maintenance, not package work. Use the `skeleton-development` skill in the skeleton repository instead of this one.
5. If the implementation was delegated to a subagent, review its diff against `write-php-code`, `write-comments`, and `write-php-test` before accepting it. That delegation decision is made before writing code, not here.

## Examples

- After adding a config key, run the PHP suite (step 2) even if only `config/*.php` changed. `composer analyse` catches a mismatched type. `composer test` catches a broken merge.
- After a refactor across several classes done by a subagent, review its diff against `write-php-code` before running the PHP suite.

## Anti-Patterns

- Running the full validation suite unconditionally when only documentation changed.
- Skipping `composer test` because `composer style:fix` and `composer analyse` passed.
- Accepting a subagent's diff without checking it against the coding and testing skills.

## Related Skills

- **Write PHP Code**: [write-php-code](../write-php-code/SKILL.md)
- **Write PHP Test**: [write-php-test](../write-php-test/SKILL.md)
