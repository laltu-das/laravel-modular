# Facade reference

The `LaravelModular` facade (`Laltu\LaravelModular\Facades\LaravelModular`, alias `LaravelModular`) is the runtime API of the package. It proxies the `Laltu\LaravelModular\LaravelModular` singleton registered in the container, so you can also type-hint the class in constructors:

```php
use Laltu\LaravelModular\LaravelModular;

final class CheckoutService
{
    public function __construct(private LaravelModular $modules) {}

    public function gateways(): array
    {
        return $this->modules->module('Billing')->manifest()['gateways'] ?? [];
    }
}
```

> With multi-tenancy configured, `modules()`, `moduleNames()`, `has()`, and `module()` only expose modules **enabled for the current tenant** — see [multi-tenancy](multi-tenancy.md).

## modules()

Every module that is active for the current tenant (no `.disabled` marker, allowed by the voter), keyed by module name:

```php
foreach (LaravelModular::modules() as $name => $module) {
    echo $module->name;        // 'Billing'
    echo $module->path;        // /var/www/app/Modules/Billing
    echo $module->namespace;   // 'Modules\Billing'
}
```

## module(string $name)

Resolve one module case-insensitively; throws `Laltu\LaravelModular\Exceptions\ModuleNotFound` when it is missing or disabled:

```php
$module = LaravelModular::module('billing');       // resolves Modules\Billing
$module = LaravelModular::module('billing')->path('routes/api.php');
```

## has(string $name)

```php
if (LaravelModular::has('Billing')) {
    // safe to reference Billing's public API
}
```

## moduleNames()

```php
LaravelModular::moduleNames(); // ['Billing', 'Catalog', 'Reporting']
```

## isEnabled(Module $module)

The raw voter decision for a module (always `true` when no voter is configured):

```php
$module = app(Laltu\LaravelModular\Discovery\ModuleRepository::class)->find('Billing');

LaravelModular::isEnabled($module); // bool
```

## tenant()

The tenant resolved by the configured `TenantResolver`, or `null` when no resolver is configured:

```php
LaravelModular::tenant()?->id;
```

## publish(object $event)

Dispatch an event through Laravel's dispatcher and get every listener response as a re-indexed array (`null` when there were none):

```php
$responses = LaravelModular::publish(new InvoicePaid(amount: 2500));

// one response per listener, in registration order
$firstPdfPath = $responses[0] ?? null;
```

See [event-driven communication](event-driven-communication.md) for why modules should only ever talk through events.

## listen(string|array $events, Closure|string|null $listener = null)

Subscribe at runtime — class names, closures, arrays of events, and wildcards all work:

```php
LaravelModular::listen('Modules\Billing\Events\*', AuditBillingActivity::class);

LaravelModular::listen([InvoicePaid::class, InvoiceRefunded::class], SyncLedger::class);

LaravelModular::listen(OrderShipped::class, function (OrderShipped $event): void {
    Log::info("Order {$event->trackingNumber} shipped");
});
```

Prefer the `module.php` manifest or the `Listeners/` directory for permanent wiring; use `listen()` for dynamic subscriptions (feature flags, tests, per-tenant hooks).

## Support classes

Beyond the facade, these container bindings are public API as well:

| Binding | Purpose |
|---|---|
| `Laltu\LaravelModular\Support\CurrentTenant` | `get()` / `has()` the current tenant without the facade |
| `Laltu\LaravelModular\Discovery\ModuleRepository` | `all()` (enabled), `all(true)` (including disabled), `find($name)` |
| `Laltu\LaravelModular\Support\Module` | `has($relative)`, `path($relative)`, `class($relative)`, `manifest()` on a module |
| `Laltu\LaravelModular\Boundaries\ModuleBoundaryInspector` | programmatic boundary verification used by `module:boundaries` |

```php
use Laltu\LaravelModular\Discovery\ModuleRepository;

$evenDisabled = app(ModuleRepository::class)->all(includeDisabled: true);

$module->has('database/migrations');            // bool
$module->class('Http/Controllers/HomeController'); // 'Modules\Billing\Http\Controllers\HomeController'
$module->manifest();                            // parsed module.php array
```

## Lifecycle events

| Event | Fired when |
|---|---|
| `Laltu\LaravelModular\Events\ModuleBooting` | before each enabled module boots |
| `Laltu\LaravelModular\Events\ModuleBooted` | after each enabled module boots |
| `Laltu\LaravelModular\Events\ModuleDisabled` | `module:disable` creates the marker |
| `Laltu\LaravelModular\Events\ModuleEnabled` | `module:enable` removes the marker |

```php
use Laltu\LaravelModular\Events\ModuleBooted;

Event::listen(function (ModuleBooted $event): void {
    // $event->module  — Laltu\LaravelModular\Support\Module
    // $event->tenant  — resolved tenant or null
});
```
