<div align="center">
    <h1>Laravel Modular</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/laltu/laravel-modular"><img src="https://img.shields.io/packagist/v/laltu/laravel-modular.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/laltu/laravel-modular"><img src="https://img.shields.io/packagist/php-v/laltu/laravel-modular.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/laltu/laravel-modular"><img src="https://badge.laravel.cloud/badge/laltu/laravel-modular?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/laltu/laravel-modular/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/laltu/laravel-modular/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/laltu/laravel-modular"><img src="https://img.shields.io/packagist/dt/laltu/laravel-modular.svg?style=flat-square" alt="Total Downloads"></a>
</p>



## Installation

You can install the package via Composer:

```bash
composer require laltu/laravel-modular
```

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="laravel-modular"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="laravel-modular-config"
```

### Publishing and Running the Migrations

```bash
php artisan vendor:publish --tag="laravel-modular-migrations"
php artisan migrate
```

### Publishing the Views

```bash
php artisan vendor:publish --tag="laravel-modular-views"
```

### Publishing the Translations

```bash
php artisan vendor:publish --tag="laravel-modular-lang"
```

### Publishing the Public Assets

```bash
php artisan vendor:publish --tag="laravel-modular-assets"
```

## Usage

<!-- Add a basic usage example here. -->

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Laravel Modular! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [laltu](https://github.com/laltu)
- [All Contributors](../../contributors)

## License

Laravel Modular is open-sourced software licensed under the [MIT license](LICENSE.md).
