<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Events\OrderShipped;
use Modules\Catalog\Providers\CatalogServiceProvider;

it('honours disabled auto-discovery toggles', function () {
    expect(Route::has('catalog.index'))->toBeFalse()
        ->and(Route::has('catalog.status'))->toBeFalse()
        ->and(app('events')->hasListeners(OrderShipped::class))->toBeFalse()
        ->and(app()->getProvider(CatalogServiceProvider::class))->toBeNull()
        ->and(app()->bound('catalog.booted_by_provider'))->toBeFalse()
        ->and(view()->exists('catalog::catalog'))->toBeTrue()
        ->and(config('catalog.enabled'))->toBeTrue();
});
