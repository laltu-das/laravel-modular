# Asynchronous Communication (Messaging)

Modules communicate asynchronously by publishing **messages** to a message broker (Laravel's queue system). This provides loose coupling — the publisher doesn't need to know who consumes the message, and the consumer doesn't need to be available when the message is sent.

## How It Works

1. **Module A** publishes a message (fire-and-forget) via `MessageBus`
2. **Message broker** (queue) stores the message
3. **Module B** consumes the message via a queue worker/job
4. Modules only share **message contracts** (in `Contracts/` or `Messages/`)

## Defining Messages

Messages implement the `Message` contract. Place them in `Contracts/` (public API) or `Messages/` (internal):

```php
// Modules/Orders/Contracts/OrderPlaced.php
namespace Modules\Orders\Contracts;

use Laltu\Modular\Communication\Asynchronous\BaseMessage;

final readonly class OrderPlaced extends BaseMessage
{
    public function __construct(
        public string $orderId,
        public int $customerId,
        public float $total,
        public array $items,
    ) {}

    public function channel(): string
    {
        return 'orders';
    }

    public function priority(): int
    {
        return 10; // High priority
    }
}
```

Or as a contract interface (for stricter public API):

```php
// Modules/Orders/Contracts/OrderPlaced.php
namespace Modules\Orders\Contracts;

use Laltu\Modular\Communication\Asynchronous\Message;
use JsonSerializable;

final readonly class OrderPlaced implements Message, JsonSerializable
{
    public function __construct(
        public string $orderId,
        public int $customerId,
        public float $total,
        public array $items,
    ) {}

    public function channel(): string
    {
        return 'orders';
    }

    public function jsonSerialize(): array
    {
        return [
            'order_id' => $this->orderId,
            'customer_id' => $this->customerId,
            'total' => $this->total,
            'items' => $this->items,
        ];
    }
}
```

## Creating Messages

```bash
# Create in Messages/ directory (internal)
php artisan make:message OrderPlaced --module=Orders --channel=orders

# Create in Contracts/ directory (public API)
php artisan make:message OrderPlaced --module=Orders --channel=orders --contract
```

## Publishing Messages

```php
use Laltu\Modular\Facades\LaravelModular;
use Modules\Orders\Contracts\OrderPlaced;

// Fire-and-forget
LaravelModular::publishMessage(new OrderPlaced(
    orderId: 'ORD-123',
    customerId: 456,
    total: 99.99,
    items: [...]
));

// With delay (processed after 60 seconds)
LaravelModular::publishMessageLater(
    new OrderPlaced(...),
    60
);

// Batch publishing
$messages = [
    new OrderPlaced(...),
    new OrderPlaced(...),
];
LaravelModular::messageBus()->publishBatch($messages);
```

## Consuming Messages

### Option 1: Queue Job (Recommended)

Create a job that implements `ShouldQueue`:

```php
// Modules/Inventory/Jobs/ProcessOrderPlaced.php
namespace Modules\Inventory\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Orders\Contracts\OrderPlaced;

final class ProcessOrderPlaced implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly OrderPlaced $message,
    ) {}

    public function handle(): void
    {
        // Reserve inventory for the order
        foreach ($this->message->items as $item) {
            // Update stock...
        }
    }
}
```

Register the consumer in your module's service provider:

```php
// Modules/Inventory/Providers/InventoryServiceProvider.php
namespace Modules\Inventory\Providers;

use Illuminate\Support\ServiceProvider;
use Laltu\Modular\Communication\Asynchronous\MessageBus;
use Modules\Inventory\Jobs\ProcessOrderPlaced;
use Modules\Orders\Contracts\OrderPlaced;

final class InventoryServiceProvider extends ServiceProvider
{
    public function boot(MessageBus $messageBus): void
    {
        // Subscribe the job to handle the message
        $messageBus->subscribe(OrderPlaced::class, ProcessOrderPlaced::class);
    }
}
```

### Option 2: Closure Handler

```php
// Modules/Notifications/Providers/NotificationsServiceProvider.php
namespace Modules\Notifications\Providers;

use Illuminate\Support\ServiceProvider;
use Laltu\Modular\Communication\Asynchronous\MessageBus;
use Modules\Orders\Contracts\OrderPlaced;

final class NotificationsServiceProvider extends ServiceProvider
{
    public function boot(MessageBus $messageBus): void
    {
        $messageBus->subscribe(OrderPlaced::class, function (OrderPlaced $message): void {
            // Send confirmation email
            // This runs in a queue worker
        });
    }
}
```

### Option 3: MessageHandler Interface

```php
// Modules/Analytics/Handlers/OrderPlacedHandler.php
namespace Modules\Analytics\Handlers;

use Laltu\Modular\Communication\Asynchronous\Message;
use Laltu\Modular\Communication\Asynchronous\MessageHandler;
use Modules\Orders\Contracts\OrderPlaced;

final class OrderPlacedHandler implements MessageHandler
{
    public function handles(): string
    {
        return OrderPlaced::class;
    }

    public function handle(Message $message): void
    {
        /** @var OrderPlaced $message */
        // Track analytics event
    }
}
```

Register in service provider:

```php
// Modules/Analytics/Providers/AnalyticsServiceProvider.php
public function boot(MessageBus $messageBus): void
{
    $handler = new OrderPlacedHandler();
    $messageBus->subscribe($handler->handles(), [$handler, 'handle']);
}
```

## Message Options

```php
final readonly class OrderPlaced extends BaseMessage
{
    // ...

    public function channel(): string
    {
        return 'orders'; // Queue name
    }

    public function priority(): int
    {
        return 10; // Higher = more urgent (default: 0)
    }

    public function delay(): int
    {
        return 0; // Delay in seconds (default: 0)
    }

    public function connection(): ?string
    {
        return 'redis'; // Queue connection (default: null = default connection)
    }
}
```

## Queue Configuration

Configure your queues in `config/queue.php`:

```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
    ],
],
```

Run queue workers:

```bash
# Process all queues
php artisan queue:work redis --queue=orders,notifications,analytics

# Process specific queue with priority
php artisan queue:work redis --queue=orders:10,notifications:5,analytics:1
```

## Consuming Messages (Message Consumer)

The `message:consume` command is a long-running process that polls the queue for messages and dispatches them to registered handlers:

```bash
# Consume all subscribed queues
php artisan message:consume

# Consume specific queue
php artisan message:consume --queue=orders

# Use specific connection
php artisan message:consume --connection=redis

# With timeout (useful for testing/CI)
php artisan message:consume --timeout=60

# Custom memory limit and sleep interval
php artisan message:consume --memory=256 --sleep=5
```

In production, run this command under a process manager like Supervisor:

```ini
[program:laravel-message-consumer]
command=php artisan message:consume --connection=redis
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/message-consumer.log
```

## Monitoring Queues

```bash
# Show all queue sizes
php artisan module:queues

# Show specific queue
php artisan module:queues --queue=orders

# With specific connection
php artisan module:queues --connection=redis --queue=orders
```

## Benefits

- **Loose Coupling**: Publisher doesn't know consumers exist
- **High Availability**: Messages persist even if consumers are down
- **Scalability**: Multiple consumers can process messages in parallel
- **Resilience**: Built-in retry, dead letter queues, delayed processing
- **Fire-and-Forget**: Publisher continues immediately

## Drawbacks

- **Increased Complexity**: Requires queue infrastructure (Redis, database, etc.)
- **Eventual Consistency**: No immediate confirmation of processing
- **Message Broker as SPOF**: If queue fails, communication stops
- **Debugging**: Harder to trace async flows
- **Ordering**: Not guaranteed without FIFO queues

## When to Use

- Cross-module operations that don't need immediate results
- Operations that can be retried (email sending, webhooks, analytics)
- When modules may be deployed/scaled independently
- High-throughput scenarios
- Integration with external systems

## When to Avoid

- Simple request-response operations
- When you need immediate results
- Operations that must be transactional with the caller
- Very low-latency requirements

## Message vs Event

| Aspect | Events | Messages |
|--------|--------|----------|
| **Timing** | Synchronous (default) | Asynchronous |
| **Delivery** | Direct to listeners | Via queue broker |
| **Persistence** | In-memory only | Persistent (configurable) |
| **Retry** | Manual | Automatic |
| **Coupling** | Loose (event-based) | Looser (queue-based) |
| **Use Case** | Immediate reactions | Background processing |

> **See also:** [Event-driven Communication](event-driven-communication.md) for synchronous event-based communication.