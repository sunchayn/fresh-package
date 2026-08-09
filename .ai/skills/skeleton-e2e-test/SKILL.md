---
name: skeleton-e2e-test
description: "Run the full end-to-end verification of this Laravel package skeleton across every choice and every supported Laravel version, with static checks against the choice intent map and a written report."
license: MIT
metadata: {author: sunchayn}
---

# Skeleton E2E Test

## Primary Goal

Prove every Choice in `.template/Choices/` produces the tree, and only the tree, the choice intent map says it should, under every supported Laravel version, without ever mutating the main working tree or the skeleton's own baseline.

## Before you start

Read `choice-intent-map.md` in this skill's directory. It is the source of truth for step 1 and for the static verification phase. Do not start testing before step 1 passes.

## Two `.ai/` folders exist. Do not confuse them.

- The skeleton repo's own root `.ai/` holds guidance for developing the skeleton itself. No Choice reads it, writes it, or is tested against it. Do not assert against it. Do not copy it. Do not treat its content as a Choice's output.
- `.template/stubs/ai-support/.ai/` is the stub. `AiSupportChoice::onSelect` copies it to `.ai/` at the root of the tree `template:init` runs in. Every other Choice that touches a `.ai/...` path edits that copy, inside the sandbox, not the skeleton repo.
- Resolve every `.ai/GUIDELINES.md` or `.ai/skills/...` check against `.template/.sandbox/<profile>/.ai/...` inside the worktree. Do not resolve it against the skeleton repo's own root `.ai/`. That file holds different content. No Choice touches it. Checking it there gives a false pass or a false fail.

## Step 1. Freshness check, stop here if it fails

1. List every file under `.template/Choices/PackageFeature/` and `.template/Choices/RepositorySettings/`. For each, read its `key()` method's return value.
2. Compare that list of keys against the rows in `choice-intent-map.md`.
3. If a key exists in the codebase with no row in the map, or a row exists in the map with no matching key anywhere, stop. Do not run anything.
4. Report exactly which keys are missing from which side, and ask whether to update `choice-intent-map.md` before continuing, or to proceed anyway with reduced coverage for the drifted keys.
5. If every key matches, record "IN SYNC" for the report and continue.
6. Find every Choice whose `onSelect` or `onDecline` references a literal path under `.ai/`. Confirm its `dependsOn()` returns `AiSupportChoice::class`, unless the Choice is `AiSupportChoice` itself. A Choice without this dependency can run before `.ai/` exists in the tree. Chisel then no-ops silently instead of failing. Without this check, a missing dependency never shows up as a test failure.
7. Report any Choice that fails this check. Name the Choice and the path. Stop before running anything. This is the same bug class that let `AutoReleaseChoice` and `BoostSkillChoice` both edit a path that did not exist yet. An explicit audit caught it, not a test run.

This check exists because the map is statically maintained. A silently stale map produces a report that looks green while missing whatever changed.

## Step 2. Set up disposable worktrees, one per supported Laravel version

1. Read `composer.json`'s `scripts` section for every `switch:lXX` entry. That is the list of Laravel versions to test.
2. Do not hardcode a version list, it changes over time.
3. For each version, create a detached worktree from `HEAD`, never from `main` by name, since the current branch may not be `main`.
4. Use `git worktree add --detach ../fresh-package-lXX HEAD` for each version.
5. Never run `template:init`, `composer install`, or `composer update` against the primary working tree.
6. Every command in this skill runs inside a worktree or a sandbox subdirectory of one.

## Step 3. Prepare each worktree, do not test its baseline

1. In each worktree, run `composer install --no-scripts --quiet`.
2. Switch the Laravel version with the underlying command, not the composer script alias, and keep `--no-scripts` on it too, for example `composer update --with=illuminate/support:^12 --no-scripts --quiet`.
3. Using `composer switch:lXX` directly is wrong here. It runs a plain `composer update` with no `--no-scripts`, which re-fires the install and update hooks and runs the real `template:init` against the worktree itself.
4. Do not run `composer test`, `composer style:check`, or `composer analyse` against the worktree base.
5. That is not what this skill verifies, and `tools/phpstan/phpstan.neon.dist` lists `.template/` as a scanned path, so analysing the raw worktree either finds unrelated scaffolding debt or nothing meaningful.
6. The only things this skill tests are sandboxed, configured profiles, never the worktree base itself.

## Step 4. Create every profile needed for full coverage, inside the worktree, before running anything

1. Read `.template/Commands/TemplateSandboxCommand.php`'s `PROFILES` constant in the worktree. Those are already registered and need no setup.
2. From the keys confirmed in step 1, work out which choices the registered profiles do not exercise on their own, in both states.
3. At minimum, for every choice not already toggled both ways by a registered profile, add one profile selecting it and one declining it.
4. Always add one profile per pair of mutually exclusive choices, selecting both, expected to fail `template:init` outright with no mutation to the tree.
5. Always add one profile per `dependsOn()` edge case found in the Choice classes. Read the dependency's docblock or comment to find which hook, `onSelect` or `onDecline`, actually touches the dependency's output, then build the profile that puts that exact hook in play. Selecting the dependent only exercises the edge case if its `onSelect` is the one touching the dependency's output. If its `onDecline` is the one touching it instead, the profile needs the dependency selected and the dependent declined, isolated from any other choice whose own hooks touch the same file, or the risky edit could pass by coincidence rather than by the ordering actually working.
6. Add these as temporary entries to that worktree's own copy of `TemplateSandboxCommand::PROFILES`.
7. This is a throwaway edit to a git worktree. Never commit it, and never port it back to the main tree.
8. The permanent registered profile list is a separate decision that belongs to whoever maintains the skeleton, not to a test run.
9. Record every profile run this way, registered or created on the spot, in the report's "Profiles run" section before executing step 5.

## Step 5. Run the matrix

1. Run `php .template/init template:sandbox --matrix` in each worktree.
2. That mirrors the repo into `.template/.sandbox/<profile>`, installs with `--no-scripts`, runs `template:init --no-interaction` with each profile's flags, then `composer test`, `composer style:check`, and `composer analyse` inside that sandbox.
3. Run every expected-to-fail profile individually, outside `--matrix`, and confirm the failure message matches what the Choice classes actually enforce, not just that it failed.
4. Run all worktrees, and all profiles within the tolerance of your environment, in parallel and in sub-agents. Nothing in this flow needs to be sequential, every sandbox is an independent directory.
5. Record every result, pass or fail with reason, in the report's "Matrix results" section.

## Step 6. Static verification, one generated tree at a time

For every profile that succeeded, walk `choice-intent-map.md` row by row against that profile's actual flags.

1. Check every universal invariant first.
2. For every choice the profile selected, confirm its "present when selected" artifacts exist.
3. For every choice the profile declined, confirm its "removed when declined" artifacts are actually gone, not just that the feature looks disabled.
4. Where two choices share a resource, as `blade`/`vue` and `assets`/`vue` do today, confirm the shared resource's fate matches the combined state of both, not just one.
5. Beyond the map, use your own judgement. The map is just a basis.
6. If something looks wrong that the map does not explicitly cover, for example a duplicated registration, a dangling reference, an empty file, or a namespace that would not actually resolve at runtime, check it and log it.
7. For every literal path string passed to `$chisel->file()`, `$chisel->files()`, or `$chisel->copyDirectory()`, confirm the path resolves inside the sandbox after mirroring. Do not trust that the Choice ran without error. Chisel no-ops silently on a missing path. A passing run and a silent no-op look identical unless you check the result. Cross-check every `.ai/...` literal against `.template/stubs/ai-support/.ai/`'s structure, per the section above. Cross-check every other literal against the stub or base tree its `copyDirectory` call actually seeds from.
8. Open every markdown file a Choice edited with `removeLinesContaining`, `removeSection`, or `removeMarkdownSection`. Read it whole, not just the diff. Confirm no sentence is cut off mid-thought and no list is missing an item it should still have.
9. Check each removed line on its own merit before accepting the removal. A line that mentions the declined feature alongside other, unrelated content must stay, with only the feature's own words removed, or the Choice needs a narrower match instead of a whole-line delete. For example, a line listing "config file, routes, the Laravel Boost skill, Blade frontend" covers four features. `removeLinesContaining('boost')` deletes the whole line, not just the Boost mention. That is wrong, and it is the same mistake this skill exists to catch.
10. Flag any line that reads like a broken sentence, an incomplete list, or an orphaned fragment after the edit.
11. State plainly when a check came from your own judgement rather than the map.
12. Log every check performed as a short line, stating what was checked and what was found, whether it passed or not.
13. This is not optional and it is not a summary. The report must contain the actual log, not a restatement that everything looked fine.

## Step 7. Write the report

1. Copy `report-template.md` from this skill's directory.
2. Fill every section using the log kept during steps 1, 4, 5, and 6, in short lines, in the format `example-report.md` demonstrates.
3. If step 1 stopped early, still produce a report, status BLOCKED, with only the "Freshness check" section filled in and the reason.
4. Save it to `.ai/artifacts/E2E_REPORT_<DATE>.md`, using today's actual date.
5. End every report, pass or fail, by asking whether to remove the worktrees created in step 2.
6. Never remove worktrees without that confirmation.

## Anti-Patterns

- Testing before the freshness check passes.
- Running `composer test`, `style:check`, or `analyse` on a worktree's own base instead of only inside sandboxed profiles.
- Reimplementing `MirrorsProjectFiles`' mirroring logic by hand in a shell script. Extend `TemplateSandboxCommand` in the worktree instead, it already does this correctly.
- Hardcoding the Laravel version list or the extra profile list instead of deriving both from the current codebase each run.
- Committing the temporary profiles added in step 4, or porting them back into the main tree without being asked.
- A report that states a check passed without logging what was actually checked.
- Removing worktrees without asking first.
- Asserting against, or copying from, the skeleton repo's own root `.ai/` folder instead of the sandbox's `.ai/`, seeded from `.template/stubs/ai-support/.ai/`. They hold unrelated content.
- Trusting that a Choice's `onSelect` or `onDecline` ran without throwing as proof it changed something. Chisel no-ops silently on a missing path. Check the resulting file, not just the exit code.
- Accepting a whole-line delete on a declined feature's keyword without reading what else the line covers. A line about several features needs a narrower edit, not a full-line removal.

## References
- choice-intent-map.md
- report-template.md
- example-report.md
- .template/Commands/TemplateSandboxCommand.php
- .template/Commands/Concerns/MirrorsProjectFiles.php
- .template/Choices
- .template/stubs/ai-support/.ai/, the stub every `.ai/...` path a Choice touches actually resolves from
