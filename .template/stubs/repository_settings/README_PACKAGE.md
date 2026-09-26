# :package_name

[![Latest Version on Packagist](https://img.shields.io/packagist/v/:vendor_slug/:package_slug.svg?style=flat-square)](https://packagist.org/packages/:vendor_slug/:package_slug)
[![License](https://img.shields.io/packagist/l/:vendor_slug/:package_slug.svg?style=flat-square)](https://packagist.org/packages/:vendor_slug/:package_slug)
[![PHP Version](https://img.shields.io/packagist/php-v/:vendor_slug/:package_slug.svg?style=flat-square)](https://packagist.org/packages/:vendor_slug/:package_slug)
[![codecov](https://codecov.io/github/:vendor_slug/:package_slug/graph/badge.svg?token=IPMYSPI2T4)](https://codecov.io/github/:vendor_slug/:package_slug)
<a href="https://packagist.org/packages/:vendor_slug/:package_slug"><img src="https://badge.laravel.cloud/badge/:vendor_slug/:package_slug?style=flat" alt="Laravel versions"></a>

:package_description

---

## Installation

You can install the package via Composer:

```bash
composer require :vendor_slug/:package_slug
```

<!-- @chisel-publish-intro -->
You may publish all the package's resources at once with:

```bash
php artisan vendor:publish --tag=":package_slug"
```

Or, you may publish each resource individually:

<!-- @end-chisel-publish-intro -->
### Publishing the Configuration File

```bash
php artisan vendor:publish --tag=":package_slug-config"
```

### Publishing and Running the Migrations

```bash
php artisan vendor:publish --tag=":package_slug-migrations"
php artisan migrate
```

### Publishing the Views

```bash
php artisan vendor:publish --tag=":package_slug-views"
```

### Publishing the Translations

```bash
php artisan vendor:publish --tag=":package_slug-lang"
```

### Publishing the Public Assets

```bash
php artisan vendor:publish --tag=":package_slug-assets"
```

## Usage

<!-- Add a basic usage example here. -->

## Links

- [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.
- [Contributing Guide](.github/CONTRIBUTING.md) Thank you for considering contributing to :package_name!
- [The security policy](.github/SECURITY.md) for reporting security vulnerabilities.

## Credits

- [:author_name](https://github.com/:author_username)
- [All Contributors](../../contributors)

## License

:package_name is open-sourced software licensed under the [MIT license](LICENSE.md).
