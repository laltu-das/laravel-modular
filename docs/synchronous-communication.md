# Synchronous Communication (Method Calls)

Modules can communicate synchronously by calling methods on each other's **public APIs** — interfaces defined in the `Contracts/` directory. This is the simplest and fastest communication pattern, equivalent to Spring Modulith's direct module access.

## How It Works

1. **Module B** defines its public API as interfaces in `Modules/Billing/Contracts/`
2. **Module B** binds implementations in its service provider
3. **Module A** resolves the interface via the service container and calls methods directly

## Defining a Public API

Create interfaces in your module's `Contracts/` directory:

```php
// Modules/Billing/Contracts/InvoiceGateway.php
namespace Modules\Billing\Contracts;

interface InvoiceGateway
{
    public function charge(int $amount, string $currency = 'USD'): bool;

    public function refund(string $invoiceId): bool;

    public function getStatus(string $invoiceId): string;
}
```

Implement the interface in your module's internal code:

```php
// Modules/Billing/Internal/Payment/StripeGateway.php
namespace Modules\Billing\Internal\Payment;

use Modules\Billing\Contracts\InvoiceGateway;

final class StripeGateway implements InvoiceGateway
{
    public function charge(int $amount, string $currency = 'USD'): bool
    {
        // Call Stripe API
        return true;
    }

    public function refund(string $invoiceId): bool
    {
        // Process refund
        return true;
    }

    public function getStatus(string $invoiceId): string
    {
        return 'paid';
    }
}
```

Register the binding in your module's service provider:

```php
// Modules/Billing/Providers/BillingServiceProvider.php
namespace Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Billing\Contracts\InvoiceGateway;
use Modules\Billing\Internal\Payment\StripeGateway;

final class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InvoiceGateway::class, StripeGateway::class);
    }
}
```

## Consuming a Public API

### Via the `LaravelModular` Facade

```php
use Laltu\Modular\Facades\LaravelModular;
use Modules\Billing\Contracts\InvoiceGateway;

$gateway = LaravelModular::api(InvoiceGateway::class);
$gateway->charge(2500);
```

### Via Dependency Injection (Recommended)

```php
use Modules\Billing\Contracts\InvoiceGateway;

final class OrderService
{
    public function __construct(
        private InvoiceGateway $invoiceGateway,
    ) {}

    public function processOrder(int $amount): void
    {
        $this->invoiceGateway->charge($amount);
    }
}
```

### From a Specific Module

```php
// Explicitly resolve from a specific module (validates it's part of that module's public API)
$gateway = LaravelModular::apiFrom(InvoiceGateway::class, 'Billing');
```

## Checking API Availability

```php
// Check if any module provides this API
if (LaravelModular::hasApi(InvoiceGateway::class)) {
    $gateway = LaravelModular::api(InvoiceGateway::class);
}

// Get all public APIs across all modules
$allApis = LaravelModular::allApis();
// [
//     'Billing' => [
//         'Modules\Billing\Contracts\InvoiceGateway' => 'Modules\Billing\Internal\Payment\StripeGateway',
//     ],
//     'Catalog' => [
//         'Modules\Catalog\Contracts\ProductRepository' => 'Modules\Catalog\Internal\Storage\EloquentProductRepository',
//     ],
// ]

// Find which module provides an interface
$module = LaravelModular::getProviderModule(InvoiceGateway::class); // 'Billing'
```

## Listing APIs from CLI

```bash
# List all module APIs
php artisan module:apis

# List APIs for a specific module
php artisan module:apis --module=Billing
```

## Benefits

- **Speed**: In-memory method calls, no serialization overhead
- **Simplicity**: Standard PHP interfaces and Laravel's service container
- **Type Safety**: Full IDE support and static analysis
- **No Indirection**: Direct calls, easy to debug

## Drawbacks

- **Strong Coupling**: Consumer depends on provider's interface at compile-time
- **Availability**: If provider module is disabled, calls will fail
- **Blocking**: Synchronous calls block the caller

## When to Use

- Simple request-response operations
- When you need immediate results
- When modules are always deployed together
- For internal module communication where coupling is acceptable

## When to Avoid

- Cross-module operations that can be asynchronous
- When modules may be deployed independently
- When you need resilience to provider unavailability
- High-throughput scenarios where blocking is problematic

> **See also:** [Asynchronous Communication](asynchronous-communication.md) for loose-coupling alternatives.