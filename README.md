# Fresh Package

An opinionated skeleton for scaffolding a modular and declutter Laravel package. It supports backend-only and full stack packages.

## What's included

- Dependency free package.
- Modular structure documentation (in the form of AI Skills).
- Classic Tooling: PHPUnit (instead of Pest), Larastan, Rector, and Pint (default styling).
- Laravel Version switcher (composer switch:l12 or composer switch:l13).
- GitHub Actions for testing, auto-release, static analysis, auto-formatting (style and rector).
- A `workbench/` app (Orchestra Testbench) to run and click through the package on its own, with no host application needed.
- Optional, off by default: a Vue 3, Tailwind v4, Vite, TypeScript frontend.
- Optional, on by default, each removable individually:
  - Dependabot.
  - An issue template.
  - Auto release and changelog creating.
  - `FUNDING.yml` and a security policy.
- Optional, on by default:
  - AI Support (Guidelines + Skills)

## Quick start

```bash
composer create-project sunchayn/fresh-package my-package
cd my-package
```

An initialization wizard will launch and ask you a few questions to configure your new package. 

### Using GitHub create from template button

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
