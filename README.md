# Laravel Modular

A Spring Boot Modulith-style modular monolith for Laravel 12 and 13. Organize your application by feature — package-by-feature, like Spring — with convention-based auto-discovery, low-coupling event-driven communication, per-tenant module activation, and verifiable module boundaries.

## What you get

| Spring Boot / Modulith | Laravel Modular |
|---|---|
| Package-by-feature | A directory per module below `Modules/` |
| `@ComponentScan` / auto-configuration | Auto-discovery of providers, commands, listeners, policies, observers, routes, migrations, config, views, translations |
| Application events (`@EventListener`) | Module listeners auto-wired from the `handle()` type-hint, wildcard listeners, manifest listeners |
| `@Profile` (per-environment beans) | Per-tenant module activation via `TenantModuleVoter` |
| `ApplicationModules::verify()` | `php artisan module:boundaries` — public API by convention, everything else internal |
| `spring-boot-maven-plugin` scaffolding | `php artisan module:make` (aliases `make:module`, `moduler:make-module`) plus `--module` on every Laravel generator |

## Documentation

This README covers the essentials. Detailed guides live in the [docs](docs) directory:

- [Installation & setup](docs/installation.md)
- [Creating modules](docs/creating-modules.md)
- [Auto-discovery](docs/auto-discovery.md)
- [Generating resources in a module](docs/generating-resources.md)
- [Event-driven communication](docs/event-driven-communication.md)
- [Synchronous communication (method calls)](docs/synchronous-communication.md)
- [Asynchronous communication (messaging)](docs/asynchronous-communication.md)
- [Module administration](docs/module-administration.md)
- [Multi-tenancy](docs/multi-tenancy.md)
- [Module boundaries](docs/module-boundaries.md)
- [Configuration reference](docs/configuration.md)

## Requirements

- PHP 8.3 or higher
- Laravel 12.x or 13.x

## Installation

```bash
composer require laltu/laravel-modular
php artisan vendor:publish --tag=laravel-modular-config
```

## Create a module

```bash
php artisan module:make Product
# aliases:
php artisan make:module Product
php artisan moduler:make-module Product

php artisan module:list // lists each module and its Enabled/Disabled status
```

Modules live in `Modules/` by default. A module uses the same layout as a Laravel application; the module directory is the equivalent of the application's `app` directory:

```text
Modules/Product/
├── Broadcasting/
├── Casts/
├── config/product.php         merged into config('product.*')
├── Console/Commands/          auto-registered in console
├── Contracts/                 public API: interfaces other modules may use
├── Enums/                     public API
├── Events/                    public API: cross-module events
├── Exceptions/
├── Http/{Controllers,Middleware,Requests,Resources}
├── Jobs/
├── Listeners/                 auto-wired from the handle() type-hint
├── Mail/
├── Messages/                  async messages (internal)
├── Models/Scopes/
├── Notifications/
├── Observers/                 {Model}Observer => Models/{Model} (auto)
├── Policies/                  {Model}Policy => Models/{Model} (auto)
├── Providers/                 *ServiceProvider (auto-registered)
├── Rules/
├── View/Components/
├── database/{factories,migrations,seeders}
├── resources/{lang,views}
├── routes/{web,api}.php
├── tests/
└── module.php
```

The module root and namespace are configurable. The package registers the configured namespace with the module root at runtime, so a generated `Product` controller is available as `Modules\Product\Http\Controllers\ProductController`.

## Auto-discovery (convention over configuration)

For every enabled module, Laravel Modular automatically wires the following — no `module.php` entries needed:

| Convention | What happens |
|---|---|
| `config/*.php` | merged into the config repository, keyed by file name |
| `Providers/*ServiceProvider.php` | registered with the full provider lifecycle (`register` + `boot`) |
| `Console/Commands/*Command.php` | registered as Artisan commands (console only) |
| `Listeners/*.php` | event inferred from the `handle()` / `__invoke()` type-hint, then subscribed |
| `Policies/{Model}Policy.php` | `Gate::policy(Models\{Model}, {Model}Policy)` |
| `Observers/{Model}Observer.php` | `Models\{Model}::observe({Model}Observer)` |
| `routes/web.php`, `routes/api.php` | loaded as module routes |
| `database/migrations` | registered with the migrator |
| `resources/views`, `resources/lang` | loaded namespaced by module (`product::welcome`, `product::messages.*`) |

Module service providers are registered during the container's registration phase, so they behave exactly like any other provider — their own `boot()` runs and they can rely on the full lifecycle.

Every aspect can be turned off individually:

```php
// config/laravel-modular.php
'auto_discovery' => [
    'config' => true,
    'commands' => true,
    'listeners' => true,
    'migrations' => true,
    'observers' => true,
    'policies' => true,
    'providers' => true,
    'routes' => true,
    'translations' => true,
    'views' => true,
],
```

`module.php` remains for explicit declarations; manifest entries are merged and de-duplicated with auto-discovered ones:

```php
return [
    'name' => 'Product',
    'providers' => [Modules\Product\Providers\ModuleServiceProvider::class],
    'listeners' => [
        OrderPlaced::class => [ReduceStock::class],
        'Modules\Billing\Events\*' => [RecalculateAccountBalance::class], // wildcards work
    ],
];
```

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
| `make:message` | `Messages` / `Contracts` |
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
php artisan module:make Category
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
php artisan make:message OrderPlaced --module=Orders --channel=orders
php artisan make:message OrderPlaced --module=Orders --channel=orders --contract
```

For a module model, `-m`, `-f`, and `-s` place the migration, factory, and seeder in that module's `database/` directories instead of the application's global database directories.

## Event-driven communication between modules

Modules stay decoupled by talking through events — never through each other's internals:

- Events that are part of a module's **public API** live in `{Module}/Events/`.
- Listeners that **react** to another module live in the consuming module's `Listeners/` directory and are auto-wired.
- Publish with the `LaravelModular` facade (or plain `event()`):

```php
use Laltu\Modular\Facades\LaravelModular;
use Modules\Billing\Events\InvoicePaid;

LaravelModular::publish(new InvoicePaid(amount: 2500));

// subscribe at runtime, wildcards included:
LaravelModular::listen('Modules\Billing\Events\*', AuditBillingActivity::class);
```

```php
// Modules/Catalog/Listeners/ReleaseInventory.php
final class ReleaseInventory
{
    public function handle(InvoicePaid $event): void
    {
        // Catalog reacts; Billing does not know Catalog exists.
    }
}
```

## Synchronous communication (method calls)

Modules can call methods directly on each other's **public APIs** — interfaces in `Contracts/` — for fast, in-memory communication:

```php
// Module B defines its public API
// Modules/Billing/Contracts/InvoiceGateway.php
interface InvoiceGateway
{
    public function charge(int $amount): bool;
}

// Module B binds implementation in its service provider
$this->app->bind(InvoiceGateway::class, StripeGateway::class);

// Module A calls the API directly
use Laltu\Modular\Facades\LaravelModular;
use Modules\Billing\Contracts\InvoiceGateway;

$gateway = LaravelModular::api(InvoiceGateway::class);
$gateway->charge(2500);
```

[Read more →](docs/synchronous-communication.md)

## Asynchronous communication (messaging)

Modules communicate via messages on a queue (fire-and-forget) for loose coupling and resilience:

```php
// Define a message (in Contracts/ for public API)
// Modules/Orders/Contracts/OrderPlaced.php
final readonly class OrderPlaced extends BaseMessage
{
    public function __construct(
        public string $orderId,
        public float $total,
    ) {}

    public function channel(): string { return 'orders'; }
}

// Module A publishes (fire-and-forget)
LaravelModular::publishMessage(new OrderPlaced('ORD-123', 99.99));

// Module B consumes via a queue job
final class ProcessOrderPlaced implements ShouldQueue
{
    public function __construct(public readonly OrderPlaced $message) {}
    public function handle(): void { /* reserve inventory */ }
}

// Register consumer in Module B's service provider
$messageBus->subscribe(OrderPlaced::class, ProcessOrderPlaced::class);
```

[Read more →](docs/asynchronous-communication.md)

## Module lifecycle administration

```bash
php artisan module:disable Billing  # drops a .disabled marker; module is skipped
php artisan module:enable Billing   # removes the marker
php artisan module:list             # shows Enabled/Disabled status per module
```

`module:enable` and `module:disable` dispatch `ModuleEnabled` / `ModuleDisabled` events. Boot lifecycle events `ModuleBooting` / `ModuleBooted` fire per module and carry the current tenant.

## Multi-tenancy

Two hooks integrate the package with your existing `TenantContext` patterns:

1. **`tenant_resolver`**: a class implementing `TenantResolver`. It feeds the tenant payload of `ModuleBooting` / `ModuleBooted` and `LaravelModular::tenant()`.
2. **`tenant_voter`**: a class implementing `TenantModuleVoter`, deciding per tenant whether a module boots — like Spring profiles, but per tenant. Rejected modules are not registered at all: no providers, routes, listeners, or commands.

```php
// config/laravel-modular.php
'tenant_resolver' => App\Tenancy\CurrentTenantResolver::class,
'tenant_voter' => App\Tenancy\TenantModuleVoter::class,
```

```php
use Laltu\Modular\Contracts\TenantResolver;
use Laltu\Modular\Contracts\TenantModuleVoter;
use Laltu\Modular\Support\Module;

final class CurrentTenantResolver implements TenantResolver
{
    public function current(): mixed
    {
        return app(TenantContext::class)->tenant();
    }
}

final class TenantModuleVoter implements TenantModuleVoter
{
    public function allows(Module $module, mixed $tenant): bool
    {
        // e.g. plan-based activation: tenant->plan includes the POS feature
        return in_array(strtolower($module->name), $tenant?->modules ?? [], true);
    }
}
```

At runtime, `LaravelModular::modules()`, `moduleNames()`, `has()`, and `module()` only expose modules enabled for the current tenant.

## Module boundaries

Convention-based, Spring Modulith `verify()` style:

- A module's **public API** = its top-level `Contracts/`, `Events/`, and `Enums/` directories (configurable via `public_directories`).
- **Everything else is internal by default** — other modules must not reference it.
- Verify with:

```bash
php artisan module:boundaries                # exits non-zero on violations (CI-friendly)
php artisan module:boundaries --module=Billing
```

```text
Module boundary violations detected (1):
+-----------+--------------------------------+----------------------------------------------+
| Module    | File                           | References                                   |
+-----------+--------------------------------+----------------------------------------------+
| Reporting | Jobs/RecalculateLedger.php     | Modules\Invoicing\Internal\Ledger\IncomeLedger |
+-----------+--------------------------------+----------------------------------------------+
```

Expose more of a module deliberately by adding directories to `public_directories` (e.g. add `Http` to let other modules route to its controllers), or move shared code into `Contracts/` / `Events/`.

## Configuration

The published `config/laravel-modular.php` file contains the following options:

- `path`: the module directory, `base_path('Modules')` by default
- `namespace`: the module namespace prefix, `Modules` by default
- `enabled`: enable or disable module discovery
- `tenant_resolver`: optional class implementing `TenantResolver`
- `tenant_voter`: optional class implementing `TenantModuleVoter`
- `public_directories`: top-level module directories considered public API
- `auto_discovery.*`: per-aspect auto-discovery switches

The optional tenant resolver is used when dispatching `ModuleBooting` and `ModuleBooted` lifecycle events. It lets an application initialize modules for its current tenant without coupling the package to a tenancy package.

## Testing

```bash
composer test          # the full suite: phpstan, lint check, type coverage, pest
composer analyse       # static analysis only
composer lint          # fix code style with Pint
composer lint:check    # verify code style without changing files
composer test:types    # type coverage
composer test:unit     # Pest tests
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [laltu](https://github.com/laltu-das)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
