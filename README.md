# Fresh Package

An opinionated skeleton for scaffolding a modular and decluttered Laravel package. It supports backend-only and full-stack packages.

## What's included

- Native package (doesn't depend on another package to function, such as the Spatie package tool).
- Modular structure (also enforced with optional skills).
- Classic Tooling: PHPUnit (instead of Pest), Larastan, Rector, and Pint (default styling).
- Laravel Version switcher (composer switch:l12 or composer switch:l13).
- Thorough GitHub Actions (with fork submission support) for testing with coverage (Codecov), auto-release, static analysis, and auto-formatting (Pint and rector).
- A `workbench/` app (inherited from laravel package default's skeleton) to run and click through the package on its own, with no host application needed.
- Optional, off by default: a modular Vue 3, Tailwind v4, Vite, TypeScript frontend.
- Optional repository settings, on by default, each removable individually:
    - Dependabot.
    - An issue template.
    - Auto release and changelog creation.
    - Funding policy.
    - Security policy.
- Optional features, on by default:
    - AI Support (Guidelines + Skills)

## Quick start

```bash
composer create-project sunchayn/fresh-package my-package
cd my-package
```

An initialization wizard will launch and ask you a few questions to configure your new package.

### Using GitHub's `use this template` button

If you create your repo from the template, clone it locally, then run `composer install`.

The same installation wizard will launch to configure the package.

### Verification after initialization

```bash
composer test   # verify the result
composer serve  # try the package at http://localhost:8000
```

## Contributing

See [.github/CONTRIBUTING.md](.github/CONTRIBUTING.md) and the `skeleton-development` skill to learn more on how you can contribute to the template itself.

## After creating a package

- Review Dependabot pull requests before merging.
- Write commits in the Conventional Commits format, since `release-please` reads them for version bumps and changelog sections.
- Add `CODECOV_TOKEN` token from [codecov](https://about.codecov.io/codecov-free-trial/) (it is free for OSS) for code coverage reports.
