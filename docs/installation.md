# Installation & setup

## Requirements

- PHP 8.3 or higher
- Laravel 12.x or 13.x

## Install the package

Install the package through Composer:

```bash
composer require laltu/laravel-modular
```

The `LaravelModularServiceProvider` is registered automatically through Laravel's package discovery — no manual registration is required. The package also exposes a `LaravelModular` facade alias.

## Publish the configuration (optional)

Every option has a sensible default, so publishing is only needed when you want to customize something:

```bash
php artisan vendor:publish --tag=laravel-modular-config
```

This copies `config/laravel-modular.php` into your application. See the [configuration reference](configuration.md) for every available option.

## Verify the installation

```bash
php artisan module:list   # lists discovered modules and their status (empty list at first)
php artisan module:make Product
php artisan module:list   # Product now shows as Enabled
```

Modules are created below `base_path('Modules')` by default, namespaced under `Modules\`. Both the location and the namespace are configurable — the package registers the configured namespace at runtime, so no `composer.json` or `composer dump-autoload` changes are needed.

## Disabling the whole package

Set `enabled` to `false` in `config/laravel-modular.php` to switch off module discovery entirely (providers, routes, listeners, commands, and boot lifecycle events are all skipped). The package's own commands remain available.

## Next steps

- [Creating modules](creating-modules.md)
- [Auto-discovery](auto-discovery.md)
- [Generating resources in a module](generating-resources.md)
