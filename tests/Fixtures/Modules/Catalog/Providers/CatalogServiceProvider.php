<?php

declare(strict_types=1);

namespace Modules\Catalog\Providers;

use Illuminate\Support\ServiceProvider;

final class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->instance('catalog.booted_by_provider', true);
    }
}
