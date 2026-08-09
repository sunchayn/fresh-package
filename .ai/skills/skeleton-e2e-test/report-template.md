# E2E test report, {{DATE}}

Status: {{PASSED|FAILED|BLOCKED}}. {{one-sentence summary}}

## Freshness check
- Choices found in `.template/Choices/`: {{list of keys}}.
- Rows found in `choice-intent-map.md`: {{list of keys}}.
- Result: {{IN SYNC|OUT OF SYNC, stopped here}}.

## Setup
- Worktrees: {{paths}}, from commit {{sha}}.
- Laravel versions under test: {{list, from composer.json's switch:lXX scripts}}.
- PHP version: {{version}}.

## Profiles run
List every profile run, registered or created on the spot for this run, one line each.

- `{{profile-name}}` ({{registered|custom, created for this run}}): flags `{{flags}}`.
- ...

## Matrix results
Per worktree, per profile, pass or fail. One line each.

- {{laravel-version}} / `{{profile-name}}`: {{PASS|FAIL}}{{, reason if FAIL}}.
- ...

## Static verification log
One line per check performed, per profile. State what was checked and what was found, not just pass or fail. Include checks the intent map does not cover if judgement called for one.

- [{{profile-name}}] {{what was checked}}: {{what was found}}. {{OK|MISMATCH}}
- ...

## Findings
Only real issues, skip this section entirely if there are none.

- **{{short title}}.** {{what is wrong, where, and why it matters}}. {{Fixed in this run | Reported, not fixed}}.

## Worktree state
{{clean | not clean, details}}.

## Open question
{{cleanup question or next step}}.
