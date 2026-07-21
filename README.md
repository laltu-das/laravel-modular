# Laravel Modular

A lightweight, convention-first modular package for Laravel 12 and 13. Keep each feature in its own module while using the folders, namespaces, and generators Laravel developers already know.

## Installation

```bash
composer require laltu/laravel-modular
php artisan vendor:publish --tag=laravel-modular-config
```

## Create a module

```bash
php artisan make:module School
php artisan module:list
```

Modules live in `Modules/` by default. A module uses the same layout as a Laravel application; the module directory is the equivalent of the application's `app` directory:

```text
Modules/School/
├── Broadcasting/
├── Casts/
├── Console/Commands/
├── Contracts/
├── Enums/
├── Events/
├── Exceptions/
├── Http/{Controllers,Middleware,Requests,Resources}
├── Jobs/
├── Listeners/
├── Mail/
├── Models/Scopes/
├── Notifications/
├── Observers/
├── Policies/
├── Providers/ModuleServiceProvider.php
├── Rules/
├── View/Components/
├── database/{factories,migrations,seeders}
├── resources/{lang,views}
├── routes/{web,api}.php
├── tests/
└── module.php
```

The module root and namespace are configurable. The package registers the configured namespace with the module root at runtime, so a generated `School` controller is available as `Modules\School\Http\Controllers\SchoolController`.

## Generate resources in a module

All supported Laravel generators gain a `--module` option. Without `--module`, Laravel's normal generator behavior is unchanged.

| Command | Module directory |
|---|---|
| `make:cast` | `Casts` |
| `make:channel` | `Broadcasting` |
| `make:command` | `Console/Commands` |
| `make:component` | `View/Components` |
| `make:controller` | `Http/Controllers` |
| `make:enum` | `Enums` |
| `make:event` | `Events` |
| `make:exception` | `Exceptions` |
| `make:factory` | `database/factories` |
| `make:interface` | `Contracts` |
| `make:job` | `Jobs` |
| `make:listener` | `Listeners` |
| `make:mail` | `Mail` |
| `make:middleware` | `Http/Middleware` |
| `make:model` | `Models` |
| `make:notification` | `Notifications` |
| `make:observer` | `Observers` |
| `make:policy` | `Policies` |
| `make:provider` | `Providers` |
| `make:request` | `Http/Requests` |
| `make:resource` | `Http/Resources` |
| `make:rule` | `Rules` |
| `make:scope` | `Models/Scopes` |
| `make:seeder` | `database/seeders` |
| `make:test` | `tests` |
| `make:view` | `resources/views` |

### Example

```bash
php artisan make:module Category
php artisan make:controller CategoryController --module=Category
php artisan make:model Category --module=Category -m -f -s
php artisan make:request CreateCategoryRequest --module=Category
php artisan make:job ProcessCategoryImport --module=Category
php artisan make:event CategoryCreated --module=Category
php artisan make:policy CategoryPolicy --module=Category
php artisan make:resource CategoryResource --module=Category
php artisan make:middleware EnsureCategoryActive --module=Category
php artisan make:observer CategoryObserver --module=Category
php artisan make:mail CategorySummaryMail --module=Category
```

For a module model, `-m`, `-f`, and `-s` place the migration, factory, and seeder in that module's `database/` directories instead of the application's global database directories.

## Module discovery

Every enabled directory directly below `Modules/` is discovered. Add a `.disabled` file to disable a module. For each enabled module, Laravel Modular automatically loads:

- `routes/web.php` and `routes/api.php`
- `database/migrations`
- namespaced views (`school::...`) and translations
- providers, commands, and event listeners declared by `module.php`

A generated `module.php` looks like this:

```php
return [
    'name' => 'School',
    'providers' => [Modules\School\Providers\ModuleServiceProvider::class],
    'listeners' => [
        StudentEnrolled::class => [SendWelcomeMessage::class],
    ],
];
```

Use regular Laravel events and listeners inside modules. Modules can listen to each other's events through the `listeners` declaration without introducing a separate event abstraction.

## Configuration

The published `config/laravel-modular.php` file contains the following options:

- `path`: the module directory, `base_path('Modules')` by default
- `namespace`: the module namespace prefix, `Modules` by default
- `enabled`: enable or disable module discovery
- `tenant_resolver`: optional class implementing `TenantResolver`

The optional tenant resolver is used when dispatching `ModuleBooting` and `ModuleBooted` lifecycle events. It lets an application initialize modules for its current tenant without coupling the package to a tenancy package.

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
