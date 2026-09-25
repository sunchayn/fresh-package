---
name: write-php-test

description: "Use this skill for writing, editing, fixing, or reviewing package tests with PHPUnit, Paratest, and Orchestra Testbench. Covers TDD, where a test belongs, <!-- @chisel-workbench -->workbench behavior,<!-- @end-chisel-workbench --> commands, routes, config, migrations, publishable resources, and a test's naming, block structure, and mocking conventions."

license: MIT

metadata:
  author: sunchayn

---

# Write PHP Test

## Primary Goal

Prove package behavior with PHPUnit, Paratest, and Orchestra Testbench, formatted the same way every time, so a reader can tell what is being set up, what is being exercised, and what is being asserted without reading the test body top to bottom.

## Workflow

1. Start with TDD. Write the smallest failing package test for the requested behavior. Implement the smallest change that makes it pass.
2. Cover happy-path, unhappy-path, and edge-case behavior when the feature has meaningful failure modes.
3. Name the file by suffix, not by folder: `*UnitTest.php` under `tests/App` for isolated logic that needs no framework boot, `*FunctionalTest.php` under `tests/App` for behavior that needs the container, config, or routes, `*IntegrationTest.php` under `tests/Integration` for behavior that tests the application layer such as Endpoints, Console Commands, etc. or components integrations beyond what is tested in the functional tests. `tests/phpunit.xml.dist` maps each suite by that suffix. Every suite except pure Unit tests boots through `tests/TestCase.php`.
4. Mirror `src/` inside `tests/App` and `tests/Integration`. A class at `src/Actions/PublishArticleAction.php` gets its test at `tests/App/Actions/PublishArticleActionFunctionalTest.php`, so a reader can find either file from the other by path alone.
5. When a route file has more than one test method, keep each test method scoped to one endpoint. Add a bare `test_it_integrates(): void` method only when the file's other tests mock or stub the route's dependencies, asserting only status code and a basic response shape. Skip it when nothing in the file is mocked, a redundant bare test adds nothing.
6. Name test methods `test_it_does_the_thing(): void`, describing observable behavior, not implementation.
7. Structure the body as Arrange, Act, Assert, marked with exactly those block comments. Add `// Anticipate` between Arrange and Act only when setting mock or spy expectations. Leave one blank line after each block comment, and one blank line between independent statements inside a block. Combine Act and Assert into a single `// Act & Assert` block only for a one-line exercise-and-check test.
8. Resolve the class under test with `resolve(ClassName::class)` or `$this->app->make()` rather than `new ClassName(...)`, unless the test is a dependency-free Unit test.
9. Build test data with model factories. Use relationship helpers (`->for($model)`) instead of setting a foreign key by hand, and factory state methods (`->published()`) instead of raw attribute overrides. Put a factory call whose result is used later on its own line, with `->create()` on a following indented line. Capture a value the Act or Assert block needs later inline in the factory array, such as `'published_at' => $now = CarbonImmutable::now()`, instead of declaring it above the factory call. Create unrelated filler rows inline with no variable at all, so the Arrange block only names what the test actually uses. Seed several models with `->count(N)->create()` plus array destructuring (`[$first, $second] = ...`), or `forEachSequence()` when each one needs a different state.
10. Prefer `spy()` over `mock()` unless the test needs an expectation set in advance (anticipate block). Create the spy/mock before resolving the class that consumes it, and assert with `shouldHaveReceived(...)`. In non-unit tests use Laravel's IoC for creating the spy/mock. When the assertion needs to inspect an argument, pass a typed callback to `withArgs()`, assert inside it, and return `true`:
    ```php
    $actionSpy
        ->shouldHaveReceived('execute')
        ->once()
        ->withArgs(function (PublishArticleData $data) use ($article): bool {
            $this->assertTrue($article->is($data->article));

            return true;
        });
    ```
11. For a list, scope, or ordering assertion, pluck the relevant property and compare arrays with `assertEqualsCanonicalizing()` instead of asserting membership one item at a time. For an Eloquent model's presence or absence, use `assertModelExists()`/`assertModelMissing()` instead of a raw database assertion.
12. Use `Generator`. They should return string-keyed data providers for parameterized cases. Each case, must have associative array for the values. For array with multiple items, each item must be on its own line. When providing cases where there are multi-line array (per case), leave one empty line after each case.
13. Run `composer test --filter <name>` while iterating.

## Examples

- To test config merge and override, assert the default package config value first. Then override the value in the Testbench app and assert the new value.
- To test publishable assets, migrations, views, lang files, or config, invoke the vendor publish behavior. Assert the target path exists.
- Test routes with Testbench HTTP requests. Test commands with Artisan assertions. Test migrations with a SQLite test database.
<!-- @chisel-workbench -->
Test workbench behavior after running `composer build`, when needed.
<!-- @end-chisel-workbench -->
- A test asserting a job was dispatched: assert the dispatch, not the job's own side effects. The job's own test suite covers those.
- A command test: `$this->artisan('example:sync')->assertSuccessful();` inside the Assert block. No output assertion is needed unless the command's output is part of its contract.

## Anti-Patterns

- Deleting real package tests because they are inconvenient.
- Relying only on smoke tests when behavior needs assertions.
- Testing implementation details when an observable package behavior is available to test instead.
- Setup code, mock expectations, or assertions left outside the four labeled blocks.
- Testing a delegated component's side effects from inside the caller's test instead of asserting the delegation itself.
- Manually instantiating a class with `new` when the container should resolve its dependencies.
- A data provider without a return type, or with positional, unnamed dataset keys.
- A bare `test_it_integrates` method in a route test file where no other test in that file mocks anything.
- Asserting database rows or columns by hand when a model-specific assertion already covers the case.

## Related Skills

- **Write PHP Code**: [write-php-code](../write-php-code/SKILL.md)
- **Task Finalization**: [task-finalization](../task-finalization/SKILL.md)
