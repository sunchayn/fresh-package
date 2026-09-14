---
name: create-dto-action

description: "Use this skill when adding a single-purpose write operation to a domain module: pairing an Action with the DTO that provisions its input. Use alongside scaffold-module, which covers when an Action or DTO earns its place at all."

license: MIT

metadata:
  author: sunchayn

---

# Create DTO and Action

## Primary Goal

Give each write operation one Action with one `execute()` method, and, only when the input needs it, one DTO that resolves everything the Action needs before it runs.

## Workflow

1. Name the Action `VerbNounAction` (for example `ChargeCustomerAction`), placed in `src/Modules/<Domain>/Actions/` per `scaffold-module`.
2. Give the Action exactly one public `execute()` method with exactly one parameter. A single scalar input stays a scalar parameter, skip the DTO.
3. Create the DTO in `src/Modules/<Domain>/DataTransferObjects/` once the Action needs more than one input. Make it `final readonly`, with constructor property promotion for every field.
4. Give the DTO a static factory method (for example `fromRequest()`) when an input needs resolving, such as finding a model from a request. The factory is where that lookup happens, the Action itself never queries for its own input.
5. Return the concrete type the Action produces. Return a type from `Contracts/` instead only when another module depends on the result across a module boundary.
6. Invoke the Action with a named argument: `$action->execute(data: $data)`. Construct a simple DTO inline at the call site rather than as a separate variable.
7. Test with `write-php-test`: assert the Action's own observable effect, not a delegated collaborator's side effect.

## Examples

- `ChargeCustomerAction::execute(ChargeCustomerData $data)`, where `ChargeCustomerData::fromRequest(Request $request)` resolves the `Customer` model and validates the amount, so `execute()` only ever touches already-resolved values.
- A `PublishArticleAction` that needs only an `Article $article` and nothing else: keep the parameter as `Article $article`, no DTO.

## Anti-Patterns

- An Action with more than one public method, or more than one parameter.
- An Action querying the database to find its own input, instead of receiving it already resolved.
- A DTO that isn't `final readonly`, or that exposes a setter instead of being constructed once.
- A DTO created for a single scalar input that could be a plain parameter.

## Related Skills

- **Scaffold Module**: [scaffold-module](../scaffold-module/SKILL.md)
- **Write PHP Code**: [write-php-code](../write-php-code/SKILL.md)
- **Write PHP Test**: [write-php-test](../write-php-test/SKILL.md)
