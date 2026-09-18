---
name: write-php-code

description: "Use this skill when writing or refactoring PHP classes in this package. Covers strict typing, explicit types, constructor promotion, and when an abstraction earns its place versus when to keep code inline. For where a class or capability belongs and how it's wired, use scaffold-module."

license: MIT

metadata:
  author: sunchayn

---

# Write PHP Code

## Primary Goal

Write PHP that reads as plain Laravel package code: strictly typed, explicit, and no more abstracted than the feature actually needs.

## Workflow

1. Use constructor property promotion for dependencies and value objects. Never leave an empty zero-parameter `__construct()`.
2. Place the class under the directory `scaffold-module` assigns to its concern (`src/Http/<Domain>`, `src/Modules/<Domain>`, `src/Console/Commands`). Use `scaffold-module` for which `src/Modules/<Domain>` subfolder it belongs in, and whether it earns one at all.
3. Prefer an explicit Laravel or Collection API (`loadRoutesFrom`, `publishes`, `Str::slug()`, `Arr::map()`, `$collection->pluck()`) over a hand-rolled equivalent or a repository-local wrapper.
4. Add a new class only when it has one responsibility a controller, command, or the service provider should not hold directly. Use `match` over nested ternaries for multi-branch conditionals.
5. Apply [write-comments](../write-comments/SKILL.md) to every comment written or reviewed.

## Examples

- Adding a value object that only wraps a single scalar: pass the scalar directly instead, unless it enforces an invariant a plain type cannot.
- Adding a second unrelated concern to an existing Action or Command: split it into its own class instead of growing one class's responsibility.
- A conditional with three or more branches: rewrite it as `match` instead of chained ternaries or `if`/`elseif`.

## Anti-Patterns

- Adding a class, interface, or config option for a single current use case, in case it is needed later.
- Formatting fixes done by hand instead of `composer style:fix`.

## Related Skills

- **Write Comments**: [write-comments](../write-comments/SKILL.md)
- **Scaffold Module**: [scaffold-module](../scaffold-module/SKILL.md)
- **Write PHP Test**: [write-php-test](../write-php-test/SKILL.md)
- **Task Finalization**: [task-finalization](../task-finalization/SKILL.md)
