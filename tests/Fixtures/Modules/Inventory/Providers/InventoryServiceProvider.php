<?php

declare(strict_types=1);

namespace Modules\Inventory\Providers;

use Illuminate\Support\ServiceProvider;
use Laltu\Modular\Communication\Asynchronous\MessageBus;
use Modules\Inventory\Contracts\OrderPlaced;
use Modules\Inventory\Jobs\ReserveInventory;

final class InventoryServiceProvider extends ServiceProvider
{
    public function boot(MessageBus $messageBus): void
    {
        $messageBus->subscribe(OrderPlaced::class, ReserveInventory::class);
    }
}