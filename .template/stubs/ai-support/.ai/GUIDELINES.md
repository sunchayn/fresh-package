# :package_name

This repository is a Laravel package. Keep the package focused, idiomatic, and easy for Laravel developers to install, test, and maintain.

## Package Conventions

- Use Laravel-native package APIs and the existing service provider shape before adding abstractions.
- Keep package names, namespaces, Composer metadata, publish tags, documentation, and examples aligned with `:vendor_slug/:package_slug`.
- Add only the files and dependencies needed for the package behavior being implemented.
- Prefer explicit Laravel package code over helper abstractions unless the extension point is real.
- Keep tests focused on observable package behavior through public APIs, service provider wiring, commands, routes, published resources, and documentation promises.

## Structure Conventions

- `src/Http/<Domain>/{Controllers,Requests,Resources}`: HTTP concerns grouped by domain rather than by transport type. An API endpoint is loaded through `routes/api.php`, a browser-facing one through `routes/web.php`. Create only the subfolder a domain's endpoint actually needs.
- `src/Modules/<Domain>/{Actions,Services,...}`: business logic grouped by domain, kept out of `Http` and `Console`. Create a domain's folder only when it has real content. Do not pre-scaffold empty modules.
- `src/Console/Commands`: package Artisan commands, registered through the provider's console-guarded `commands()` call.
- `resources/js` (present only when the frontend feature is kept): `app/` (entry point), `components/{base,common,domain,layout}`, `pages`, `composables`, `stores`, `interfaces`, `utils`, `config`, `tests`. See each folder's `README.md` for what belongs where.

## Quick Commands

- Full validation: `composer test`
- Formatting check: `composer style:check`
- Static analysis: `composer analyse`
- Tests, in parallel: `composer test:parallel`
- Switch Laravel version: `composer switch:l12` (or `l13`)
- Workbench build: `composer build`
- Workbench server: `composer serve`

## Working with Subagents

The primary session plans, wires the feature through the service provider, and reviews the result. It delegates the actual implementation to a subagent whenever a change is non-trivial, spanning multiple files, adding a capability, or refactoring existing behavior. Before calling any such change done, review the subagent's diff against `write-php-code`, `write-comments`, and `write-php-test`, then run `task-finalization`. A one-file, low-risk change does not need the split; write and verify it directly.

## Local Skills

- `scaffold-module`: use when adding a package capability or a new domain module under `src/Modules`, where the files go, which subfolders and HTTP transport layer it needs, and how it's wired through the service provider. Covers commands, migrations, routes, config, views, translations, assets, middleware, publish tags, workbench files, and console-only behavior.
- `create-dto-action`: use when adding a single-purpose write operation, pairing an Action with the DTO that provisions its input.
- `create-query`: use when adding a read-only lookup, to decide between a Query class and a plain inline Eloquent call.
- `write-php-code`: use when writing or refactoring PHP classes. Covers strict typing, explicit types, constructor promotion, and when an abstraction earns its place.
- `write-comments`: use whenever writing or reviewing a comment, in PHP or, when the frontend feature is kept, in TypeScript/Vue.
- `write-php-test`: use when writing, editing, fixing, or reviewing package tests with PHPUnit, Paratest, and Orchestra Testbench. Covers TDD, where a test belongs, and its naming, block structure, and mocking conventions.
- `task-finalization`: use before marking any change complete, to run the quality tools that match what changed.
- `package-release`: use when preparing changelog, release notes, tags, or GitHub release workflow changes.
- `package-compatibility`: use when reviewing code, dependencies, or CI against the PHP and Laravel support matrix.
- `package-generate-skill`: use when updating the bundled Boost skill from the package implementation, README, and examples.
