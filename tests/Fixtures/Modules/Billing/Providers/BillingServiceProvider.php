<?php

declare(strict_types=1);

namespace Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;

final class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->instance('billing.registered', true);
    }
}
