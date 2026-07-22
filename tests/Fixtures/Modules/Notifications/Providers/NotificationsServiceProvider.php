<?php

declare(strict_types=1);

namespace Modules\Notifications\Providers;

use Illuminate\Support\ServiceProvider;
use Laltu\Modular\Communication\Asynchronous\MessageBus;
use Modules\Inventory\Contracts\OrderPlaced;

final class NotificationsServiceProvider extends ServiceProvider
{
    public function boot(MessageBus $messageBus): void
    {
        $messageBus->subscribe(OrderPlaced::class, function (OrderPlaced $message): void {
            app()->instance('notifications.sent_for_order', $message->orderId);
            app()->instance('notifications.customer_id', $message->customerId);
        });
    }
}