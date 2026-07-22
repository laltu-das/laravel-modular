<?php

declare(strict_types=1);

namespace Modules\Inventory\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Inventory\Contracts\OrderPlaced;

final class ReserveInventory implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly OrderPlaced $message,
    ) {}

    public function handle(): void
    {
        app()->instance('inventory.reserved_for_order', $this->message->orderId);
        app()->instance('inventory.reserved_items', $this->message->items);
    }
}