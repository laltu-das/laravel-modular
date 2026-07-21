# Event-driven communication

Modules stay decoupled by talking through events — never through each other's internals. This mirrors Spring Modulith's application event model: the producing module publishes an event; consuming modules react through listeners without the producer knowing they exist.

## Rules of thumb

- Events that are part of a module's **public API** live in `{Module}/Events/` (a [public directory](module-boundaries.md) by default).
- Listeners that **react** to another module live in the consuming module's `Listeners/` directory and are [auto-wired](auto-discovery.md) from the `handle()` type-hint.
- Modules reference only each other's public events — a listener type-hinting an event from another module is exactly where coupling should live.

## Publishing

Use the `LaravelModular` facade (or plain `event()` — same dispatcher):

```php
use LaravelModular\LaravelModular\Facades\LaravelModular;
use Modules\Billing\Events\InvoicePaid;

LaravelModular::publish(new InvoicePaid(amount: 2500));
```

`publish()` returns the listener responses as a plain array (re-indexed), or `null` when the dispatcher returned no array.

## Listening

A listener in `Listeners/` is subscribed automatically based on its `handle()` (or `__invoke()`) type-hint:

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

For anything discovery cannot infer — wildcard listeners, listeners outside `Listeners/`, or listeners without a type-hint — use the `module.php` manifest:

```php
// Modules/Reporting/module.php
return [
    'listeners' => [
        InvoicePaid::class => [RecordRevenue::class],
        'Modules\Billing\Events\*' => [AuditBillingActivity::class], // wildcards work
    ],
];
```

## Runtime subscriptions

```php
LaravelModular::listen('Modules\Billing\Events\*', AuditBillingActivity::class);

LaravelModular::listen([InvoicePaid::class, InvoiceRefunded::class], SyncLedger::class);

LaravelModular::listen(OrderShipped::class, function (OrderShipped $event): void {
    // closure listener
});
```

## See also

- [Auto-discovery](auto-discovery.md) — how `Listeners/` classes are wired
- [Module boundaries](module-boundaries.md) — keeping cross-module references limited to public APIs
