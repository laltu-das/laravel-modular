# Creating modules

A module is a directory below the configured module path (`Modules/` by default) that mirrors the layout of a Laravel application. Think Spring's package-by-feature: everything a feature needs lives in one directory instead of being spread across the global `app/` tree.

## Scaffold a module

```bash
php artisan module:make Product
# aliases:
php artisan make:module Product
php artisan moduler:make-module Product
```

The command is idempotent by default: re-running it for an existing module fails with `Module [Product] already exists.` Pass `--force` to overwrite:

```bash
php artisan module:make Product --force
```

The generated module contains the conventional directory tree, a `module.php` manifest, a `Providers/ModuleServiceProvider.php`, empty `routes/web.php` and `routes/api.php` files, and a module config file (`config/product.php`).

## Directory layout

```text
Modules/Product/
├── Broadcasting/               # broadcast channels (make:channel --module=Product)
├── Casts/                      # Eloquent casts
├── config/product.php          # merged into config('product.*')
├── Console/Commands/           # auto-registered in console
├── Contracts/                  # public API: interfaces other modules may use
├── Enums/                      # public API
├── Events/                     # public API: cross-module events
├── Exceptions/
├── Http/{Controllers,Middleware,Requests,Resources}
├── Jobs/
├── Listeners/                  # auto-wired from the handle() type-hint
├── Mail/
├── Models/Scopes/
├── Notifications/
├── Observers/                  # {Model}Observer => Models/{Model} (auto)
├── Policies/                   # {Model}Policy => Models/{Model} (auto)
├── Providers/                  # *ServiceProvider (auto-registered)
├── Rules/
├── View/Components/
├── database/{factories,migrations,seeders}
├── resources/{lang,views}
├── routes/{web,api}.php
├── tests/
└── module.php
```

The module directory is the equivalent of the application's `app` directory: a generated `Product` controller is `Modules\Product\Http\Controllers\ProductController`.

## The manifest: module.php

Every key of `module.php` is optional. Auto-discovery means most modules never need one; keep it for explicit declarations:

```php
<?php

declare(strict_types=1);

return [
    'name' => 'Product',
    'providers' => [Modules\Product\Providers\ModuleServiceProvider::class],
    'commands' => [],    // extra Artisan commands not in Console/Commands
    'listeners' => [     // extra listeners not covered by Listeners/ auto-discovery
        OrderPlaced::class => [ReduceStock::class],
        'Modules\Billing\Events\*' => [RecalculateAccountBalance::class], // wildcards work
    ],
];
```

Manifest entries are **merged and de-duplicated** with auto-discovered ones, so an explicitly declared provider or listener is never registered twice.

## Namespaces and autoloading

The package registers the configured module namespace (default `Modules\`) with the configured module path at runtime using the registered Composer class loaders. Module factories and seeders are also autoloadable as `{Module}\Database\Factories\` and `{Module}\Database\Seeders\`. No changes to your application's `composer.json` are required.

## Disabling a module

Drop an empty `.disabled` marker file in the module directory (or run `php artisan module:disable Product`) and the module is skipped entirely — see [module administration](module-administration.md).
