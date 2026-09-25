# Contribution Guide

Thank you for considering contributing to `fresh-package` laravel package skeleton! Please review the following guidelines before submitting a pull request.

For significant changes, please open an issue first so we can discuss the approach.

## Process

1. Fork the project
2. Create a new branch
3. Code, test, commit, and push
4. Open a pull request detailing your changes

## Guidelines

This guide is for changing the skeleton itself, not for building a package from it.

The generic guidelines are defined as AI guidelines and Skills, so you can use that to familiarize yourself with the structure:
- [.ai/GUIDELINES.md](../.ai/GUIDELINES.md)
- [The `skeleton-development` skill](../.ai/skills/skeleton-development/SKILL.md)

### Using AI

The repository ships with AI guidelines and skills. To make use of them, first run `npx agenteq init` to sync your local agents. 
The .gitignore might get updated if you are using agents that are not git ignored yet. Feel free to commit the changes to the file.

### Testing changes with the Sandbox

Normally, you would run `php .template/init template:init` to initialize the template. However, this is a destructive command (will mutate the filesystem of the root repository). It will refuse to run if you invoked it from the root of the template repository.

That is why, you must use the sandbox command instead to test the real flow safely:

```bash
php .template/init template:sandbox backend-only
php .template/init template:sandbox with-frontend
php .template/init template:sandbox --matrix   # every profile
php .template/init template:sandbox --parallel # every profile, one process per CPU core
```

#### How it works

Each profile mirrors this repository into the git-ignored `.template/.sandbox/<profile>`, excluding `vendor`, `node_modules`, `.git`, `.ai`, and other dev-only paths.

It then runs `composer install --no-scripts`, the real `template:init --no-interaction` with the profile's flags, and `composer test`, all inside that copy. 

Run `template:sandbox` with no profile for an interactive picker, including an `interactive` option that runs `template:init`'s real prompts inside the sandbox.

#### Available Profiles

| Profile | Flags |
|---|---|
| `backend-only` | `--no-vue` |
| `with-frontend` | `--vue --no-blade` |
| `partial` | `--config --routes --facade --migrations --auto-release --security-policy --no-blade --no-translations --no-assets --no-ai-support --no-boost-skill --no-vue --no-dependabot --no-issue-template --no-funding --no-commands` |
| `all-in` | every package feature and repository setting selected, Blade instead of Vue |
| `all-out` | every package feature and repository setting declined, including both frontends |
| `interactive` | none, prompts answered by hand |

### Available Scripts

- Full validation: `composer test` or `composer test:parallel`
- Formatting Fixes: `composer style:fix`
- Rector Fixes: `composer rector`
- Static analysis: `composer analyse`
- Verify the skeleton across every choice profile: `php .template/init template:sandbox --matrix`
