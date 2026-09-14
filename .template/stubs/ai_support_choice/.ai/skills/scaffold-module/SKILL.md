---
name: scaffold-module

description: "Use this skill when adding a package capability or a new domain module: where the files go, which src/Modules subfolders it needs, and how it's wired through the service provider. Covers commands, migrations, routes, config, views, translations, assets, middleware, publish tags, workbench files, the frontend app, and console-only behavior."

license: MIT

metadata:
  author: sunchayn

---

# Scaffold Module

## Primary Goal

Add a capability, or open a new domain boundary, with exactly the files it needs right now, wired through the service provider with explicit Laravel APIs. Nothing pre-built for later.

## Workflow

1. Inspect the existing package structure, sibling examples, README setup notes, and the current service provider before creating files. Decide now whether to delegate the implementation to a subagent, per GUIDELINES.md's *Working with Subagents* section, since that decision is only useful before code is written.
2. Identify whether the request touches commands, migrations, routes, config, views, translations, assets, middleware, tests, README/contributing docs, compatibility, release flow, or a new domain module.
3. For a new domain module, name it PascalCase (for example `Billing`), confirm it isn't already a piece of an existing module under `src/Modules`, then create only the subfolder its first class needs, see *Modules* below for what each one holds and when it earns its place.
4. Create the capability's files under Laravel-native package paths, following `write-php-code`'s coding conventions. Use the configured package names, namespaces, publish tags, URLs, and badges consistently.
5. Add an HTTP transport layer only if the capability or module is reachable over HTTP: a controller under `src/Http/<Domain>/Controllers`, its request/resource pair under `src/Http/<Domain>/Requests` and `src/Http/<Domain>/Resources`, and the route in `routes/api.php` or `routes/web.php`. Console-only or internal-only work skips this entirely.
6. Wire the capability through the service provider using the patterns in *Provider Wiring* below.
7. Use `write-php-test` for coverage. Update README or contributing documentation when user-facing behavior changes. Use `package-compatibility` for matrix-sensitive changes. Use `package-release` for release tasks.
8. Add only the files needed for the requested capability. Validate with `task-finalization`, starting with the narrowest relevant command before broader checks.

## Modules

Business logic for a domain lives under `src/Modules/<Domain>/`, kept out of `src/Http` and `src/Console` so it stays testable without an HTTP request or console context. A subfolder earns its place only once a class actually needs it:

- `Actions/` and `DataTransferObjects/`: single-method `VerbNounAction` classes and the `final readonly` DTOs that provision them. See `create-dto-action` for the full workflow.
- `Services/`: logic that spans multiple Actions, or wraps a complex external SDK. Compose Actions instead of growing one service into several responsibilities.
- `Contracts/`: interfaces a domain exposes so another domain can depend on a boundary instead of a concrete class. Add one only when a second domain actually needs to cross that boundary.
- `Queries/`: read-only classes for a non-trivial read (pagination, joins, filtering, eager-loaded relations). See `create-query` for the full workflow, including when to skip the class entirely.
- `Enums/`: domain enumerations, TitleCase keys (`FavoritePerson`, not `FAVORITE_PERSON`).

## Provider Wiring

1. Keep provider wiring in `register()` or `boot()` unless extracting a method makes a real repeated concern clearer.
2. Put container bindings and `mergeConfigFrom` calls in `register()` when the host app must be able to override configuration.
3. Put resource loading in boot-time methods with Laravel-native APIs such as `loadRoutesFrom`, `loadViewsFrom`, and `loadTranslationsFrom`.
4. Guard console-only publishing and command registration with `runningInConsole()` before calling `publishes`, `publishesMigrations`, or `commands`.
5. Name publish tags with the `:package_slug-*` convention. This lets consumers target individual resource groups.
6. Add tests for the observable provider behavior. Cover merged config, loaded routes, publish tags, and command registration.

### Provider wiring anti-patterns:

- Loading host app state too early during provider registration.
- Calling `env()` outside config files. Use config values after `mergeConfigFrom` instead.
- Registering web-only concerns unconditionally when the package can run in console contexts.

## Examples

- To add an Artisan command, create the command class under `src/Console/Commands`. Register it in the `commands` array inside the `runningInConsole()` guard. Add a feature test for observable console output. Document the command if it is user-facing.
- To add a publishable migration, place the migration in `database/migrations`. Wire it through a console-guarded `publishesMigrations` call with a `:package_slug-migrations` tag. Test publish behavior with Testbench.
- To wire a new publish tag, add a `publishes` map inside the existing console-guarded publishing method. Name the tag with the `:package_slug-*` convention.
- To add an API endpoint for the `Greeting` domain, put the controller under `src/Http/Greeting/Controllers`, the request under `src/Http/Greeting/Requests`, and the resource under `src/Http/Greeting/Resources`. Add the route in `routes/api.php`. Keep any non-trivial business logic in `src/Modules/Greeting` rather than the controller.
- To add a Vue page when the frontend is kept, put the component under `resources/js/pages`. Route it in `resources/js/app/router.ts`. Put shared logic in `composables/` or `stores/` rather than in the page component.
- Adding billing logic reachable only from a scheduled command: create `src/Modules/Billing/Actions/ChargeCustomerAction.php` and wire it into the command. No `Contracts/`, no HTTP layer, until something outside `Billing` needs one.
- A second domain later needs to trigger billing without depending on its concrete Action: add the necessary contracts/events at that point, not before.

## Anti-Patterns

- Adding unused files because a package might need them later.
- Mixing package names, namespaces, config keys, or publish tags.
- Changing dependencies without approval.
- Replacing explicit Laravel package code with a helper abstraction when one feature-specific change would do.
- Creating `Actions/`, `Services/`, `Contracts/`, `DataTransferObjects/`, `Queries/`, and `Enums/` up front for a domain that has one class.
- Adding an HTTP controller, request, or resource for a module the package never exposes over HTTP.
- Building an app-style resolver or factory class to construct module classes. A package class is small enough to construct or inject directly.
- An Action querying the database to find its own input, instead of receiving it through its parameter or DTO.

## Related Skills

- **Write PHP Code**: [write-php-code](../write-php-code/SKILL.md)
- **Create DTO and Action**: [create-dto-action](../create-dto-action/SKILL.md)
- **Create Query**: [create-query](../create-query/SKILL.md)
- **Write PHP Test**: [write-php-test](../write-php-test/SKILL.md)
- **Task Finalization**: [task-finalization](../task-finalization/SKILL.md)
