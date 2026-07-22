<?php

declare(strict_types=1);

namespace Modules\Orders\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Orders\Contracts\OrderGateway;
use Modules\Orders\Internal\Order\DatabaseGateway;

final class OrdersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderGateway::class, DatabaseGateway::class);
    }
}