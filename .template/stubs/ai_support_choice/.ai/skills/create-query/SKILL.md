---
name: create-query

description: "Use this skill when adding a read-only lookup to a domain module: deciding whether it needs a Query class or a plain inline Eloquent call. Use alongside scaffold-module, which covers when the Queries/ subfolder earns its place at all."

license: MIT

metadata:
  author: sunchayn

---

# Create Query

## Primary Goal

Separate a non-trivial read from write operations, and skip the Query class entirely when the lookup is simple enough to inline.

## Workflow

1. Decide if this is a simple lookup: `find()`, `findOrFail()`, `firstOrFail()`, or a single `where(...)->first()`. If so, call it directly with Eloquent wherever the value is needed. No Query class.
2. For a non-trivial read, pagination, joins, search filtering, or multiple eager-loaded relations, create the class in `src/Modules/<Domain>/Queries/`, named with a `Query` suffix (for example `PublishedArticlesQuery`).
3. Keep the class thin: constructor property promotion for filter parameters, a private `query(): Builder` method that builds the base query, and public methods (`paginate()`, `get()`) that execute it.
4. Construct or inject the Query class directly wherever it's used. This package has no query-resolving factory to route through, a package class is small enough to construct or inject on its own.
5. Test with a `*FunctionalTest.php` under `tests/App`, per `write-php-test`, since it needs the container and database. Assert the correct records are filtered, sorted, and paginated, and that the expected relations are eager-loaded.

## Examples

```php
final class PublishedArticlesQuery
{
    public function __construct(
        private readonly ?string $topic = null,
    ) {
    }

    private function query(): Builder
    {
        return Article::query()
            ->published()
            ->with(['activeRevision'])
            ->latest('published_at')
            ->when(
                $this->topic !== null,
                fn (Builder $query) => $query->where('topic', $this->topic),
            );
    }

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }
}
```

- A controller resolving one directly: `new PublishedArticlesQuery(topic: $request->string('topic'))->paginate();`, no factory in between.

## Anti-Patterns

- Creating a Query class for a single-record lookup that `findOrFail()` already covers.
- Routing Query construction through a custom resolver or factory class.
- A Query class performing a write operation. Writes belong in an Action, see `create-dto-action`.

## Related Skills

- **Scaffold Module**: [scaffold-module](../scaffold-module/SKILL.md)
- **Create DTO and Action**: [create-dto-action](../create-dto-action/SKILL.md)
- **Write PHP Test**: [write-php-test](../write-php-test/SKILL.md)
