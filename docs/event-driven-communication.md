# Event-driven communication

Modules stay decoupled by talking through events — never through each other's internals. This mirrors Spring Modulith's application event model: the producing module publishes an event; consuming modules react through listeners without the producer knowing they exist.

## Rules of thumb

- Events that are part of a module's **public API** live in `{Module}/Events/` (a [public directory](module-boundaries.md) by default).
- Listeners that **react** to another module live in the consuming module's `Listeners/` directory and are [auto-wired](auto-discovery.md) from the `handle()` type-hint.
- Modules reference only each other's public events — a listener type-hinting an event from another module is exactly where coupling should live.

## Publishing

Use the `LaravelModular` facade (or plain `event()` — same dispatcher):

```php
use Laltu\LaravelModular\Facades\LaravelModular;
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

## End-to-end example: Billing → Catalog & Reporting

Billing publishes a paid-invoice event; Catalog releases inventory and Reporting records revenue. Neither consumer exists as far as Billing is concerned.

```php
// Modules/Billing/Events/InvoicePaid.php
namespace Modules\Billing\Events;

final readonly class InvoicePaid
{
    public function __construct(public int $amount, public string $invoiceNumber) {}
}
```

```php
// Modules/Billing/Http/Controllers/PaymentController.php
use Laltu\LaravelModular\Facades\LaravelModular;
use Modules\Billing\Events\InvoicePaid;

public function store(Request $request)
{
    $invoice = $this->payments->charge($request->validated());

    LaravelModular::publish(new InvoicePaid($invoice->amount, $invoice->number));

    return redirect()->route('billing.invoices.show', $invoice);
}
```

```php
// Modules/Catalog/Listeners/ReleaseInventory.php
namespace Modules\Catalog\Listeners;

use Modules\Billing\Events\InvoicePaid;

final class ReleaseInventory
{
    public function handle(InvoicePaid $event): void
    {
        // auto-wired from this type-hint — zero registration code
    }
}
```

```php
// Modules/Reporting/Listeners/RecordRevenue.php
namespace Modules\Reporting\Listeners;

use Modules\Billing\Events\InvoicePaid;

final class RecordRevenue
{
    public function handle(InvoicePaid $event): void
    {
        Revenue::create(['amount' => $event->amount]);
    }
}
```

Delete the Catalog module tomorrow and Billing keeps working untouched — that is the payoff of event-driven coupling.

## Queued listeners

Listeners are plain Laravel listeners, so `ShouldQueue` works as usual:

```php
use Illuminate\Contracts\Queue\ShouldQueue;

final class ReleaseInventory implements ShouldQueue
{
    public function handle(InvoicePaid $event): void
    {
        // runs on your queue workers
    }
}
```

## Using publish() responses

`publish()` returns every listener's return value, re-indexed — handy for collecting results from modules without referencing them:

```php
// any module can contribute a dashboard card through a listener
$cards = collect(LaravelModular::publish(new DashboardCardsRequested()))
    ->filter()
    ->all();
```

## See also

- [Auto-discovery](auto-discovery.md) — how `Listeners/` classes are wired
- [Module boundaries](module-boundaries.md) — keeping cross-module references limited to public APIs
- [Facade reference](facade-reference.md) — `publish()` and `listen()` signatures
