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

The package classes live under the `Laltu\LaravelModular` namespace, matching the `laltu/laravel-modular` Composer name:

```php
use Laltu\LaravelModular\Facades\LaravelModular;   // the facade
use Laltu\LaravelModular\LaravelModular;            // the underlying class (container singleton)
use Laltu\LaravelModular\Support\Module;           // one discovered module
```

### Upgrading from `LaravelModular\LaravelModular`

Earlier pre-releases used the `LaravelModular\LaravelModular` root namespace. When upgrading, update your imports — the facade alias, config key (`laravel-modular`), publish tags, and Artisan commands are unchanged:

```bash
grep -rl 'LaravelModular\\LaravelModular' app/ | xargs sed -i 's/LaravelModular\\LaravelModular/Laltu\\LaravelModular/g'
```

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

Or from Tinker, to prove the package classes resolve:

```php
$ php artisan tinker
> app(Laltu\LaravelModular\LaravelModular::class)->moduleNames();
= ["Product"]

> LaravelModular::has('Product');   // via the facade alias
= true
```

Modules are created below `base_path('Modules')` by default, namespaced under `Modules\`. Both the location and the namespace are configurable — the package registers the configured namespace at runtime, so no `composer.json` or `composer dump-autoload` changes are needed.

## Disabling the whole package

Set `enabled` to `false` in `config/laravel-modular.php` to switch off module discovery entirely (providers, routes, listeners, commands, and boot lifecycle events are all skipped). The package's own commands remain available.

## Next steps

- [Creating modules](creating-modules.md)
- [Auto-discovery](auto-discovery.md)
- [Generating resources in a module](generating-resources.md)
