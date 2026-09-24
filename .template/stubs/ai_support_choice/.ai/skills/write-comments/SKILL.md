---
name: write-comments

description: "Comment writing and wrapping rules for PHP, and for TypeScript/Vue. Apply whenever writing or reviewing a comment in this package."

license: MIT

metadata:
  author: sunchayn

---

# Write Comments

## Primary Goal

Keep comments rare, and make the ones that exist state a technical fact plainly, in three lines or fewer.

## Workflow

1. Before writing a comment, ask whether it explains why the code exists or why it works this way. If it only restates what the next line does, skip it.
2. Comment non-obvious decisions, workarounds, and dense expressions such as a regular expression. Never comment an obvious call, an obvious conditional, or a self-explanatory variable name.
3. Never reference a ticket number, issue link, or the request that produced the change. A future reader has no access to that context.
4. Write plain, direct language tied to a concrete technical fact. No metaphors, no idioms, no narrative framing.
5. Never use a semicolon or colon in a comment, except a colon inside a URL. A semicolon joining two clauses means the comment is saying too much at once. Split it into two sentences and drop whichever clause is not essential.
6. Keep an inline comment (`//`) to 3 lines or fewer. A comment that needs more belongs in a PHPDoc block above the method instead, or needs trimming to the essential fact.
7. Compose a multi-line comment as one complete thought first, then wrap it, breaking only at the end of a clause or after a comma. Never leave a trailing wrapped line with fewer than 4 words. Reword the sentence instead of forcing an uneven split.
8. A PHPDoc block attached to a class or method states its contract, parameters, return type, and thrown exceptions. It never names an internal variable or describes a step of the method body. That detail belongs in an inline comment at the line it explains.

## Examples

- Bad: `// Old rows are left alone since they're history now.` Good: `// Rows where expires_at is in the past are skipped.`
- Bad: `// Caches the response; the API is rate-limited, so repeat calls reuse it.` Good: `// Caches the response. The API is rate-limited, so repeat calls reuse it.`
- A regular expression that extracts a URL from Markdown is dense enough to justify stating what it captures, even next to a well-named variable.

## Anti-Patterns

- A comment restating a method or variable name that already says the same thing.
- An inline comment running 4 or more lines instead of moving to a PHPDoc block.
- A wrapped comment ending on a 1 or 2 word orphan line.
- A PHPDoc block that leaks a local variable name or control-flow detail from inside the method.

## Related Skills

- **Writing PHP Code**: [write-php-code](../write-php-code/SKILL.md)
