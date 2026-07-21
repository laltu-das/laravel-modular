# Laravel Modular

A lightweight, convention-first modular-monolith package for Laravel 12/13. Organize code by business capability, keep domain internals isolated, and let each module own its routes, migrations, providers, listeners and resources.

## Installation

```bash
composer require laltu/laravel-modular
php artisan vendor:publish --tag=laravel-modular-config
```

## Create a module

```bash
php artisan moduler:make-module School
php artisan moduler:list
```

Modules live in `Domains/` by default and use a DDD-friendly layout:

```text
Domains/School/
├── Application/{Commands,Queries,Listeners}
├── Contracts/                         # convention-based public API
├── Domain/{Entities,Events,Services,ValueObjects}
├── Infrastructure/
│   ├── Http/{Controllers,Requests}
│   ├── Jobs/
│   ├── Persistence/Models/
│   └── Providers/ModuleServiceProvider.php
├── database/{migrations,factories,seeders}
├── resources/{views,lang}
├── routes/{web,api}.php
└── module.php
```

The package registers the `Domains\\` PSR-4 prefix at runtime. The root path and namespace are configurable.

## Generate resources in a module

The normal Laravel generators gain a `--module` option:

### Category module

```bash
php artisan moduler:make-module Category
php artisan make:controller CategoryController --module=Category
php artisan make:model Category --module=Category -m -f -s
php artisan make:request CreateCategoryRequest --module=Category
php artisan make:request UpdateCategoryRequest --module=Category
php artisan make:job ProcessCategoryImport --module=Category
php artisan make:event CategoryCreated --module=Category
php artisan make:policy CategoryPolicy --module=Category
```

### Product module

```bash
php artisan moduler:make-module Product
php artisan make:controller ProductController --module=Product
php artisan make:model Product --module=Product -m -f -s
php artisan make:request CreateProductRequest --module=Product
php artisan make:request UpdateProductRequest --module=Product
php artisan make:job SyncProductStock --module=Product
php artisan make:event ProductCreated --module=Product
php artisan make:event ProductStockUpdated --module=Product
php artisan make:policy ProductPolicy --module=Product
```

Controllers, requests, jobs and models are placed under Infrastructure; events under Domain; and policies under Application. Existing generator behavior is unchanged when `--module` is omitted.

For module models, `-m`, `-f`, and `-s` place the migration, factory, and seeder in that module's `database/` directories instead of the application's global database directory.

## Auto-discovery

Every enabled directory directly below `Domains/` is discovered. Add a `.disabled` file to disable one. For each module Laravel Modular automatically loads:

- `routes/web.php` and `routes/api.php`
- `database/migrations`
- namespaced views (`school::...`) and translations
- providers, commands and event listeners declared by `module.php`

```php
return [
    'name' => 'School',
    'providers' => [Domains\School\Infrastructure\Providers\ModuleServiceProvider::class],
    'commands' => [],
    'listeners' => [
        StudentEnrolled::class => [SendWelcomeMessage::class],
    ],
];
```

## Events and module boundaries

Depend on `ModuleEventBus` rather than another module's implementation:

```php
use LaravelModular\LaravelModular\Contracts\ModuleEventBus;

$bus->publish(new StudentEnrolled($studentId));
```

`Contracts`, `Application`, and `Domain` are public by convention. `LaravelModular::isPublic($class)` can be used by architecture tests to reject dependencies on another module's Infrastructure classes. Configure `public_directories` to tighten that policy.

## Multi-tenancy

Implement `TenantResolver` and set its class in `tenant_resolver` config. The current tenant is included in `ModuleBooting` and `ModuleBooted`, allowing tenant-aware modules or existing TenantContext implementations to initialize without coupling this package to a tenancy vendor.

```php
use LaravelModular\LaravelModular\Contracts\TenantResolver;

final class CurrentTenantResolver implements TenantResolver
{
    public function current(): mixed
    {
        return app(TenantContext::class)->tenant();
    }
}
```

## License

MIT
