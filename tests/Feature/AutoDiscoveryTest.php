<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use LaravelModular\LaravelModular\Exceptions\ModuleNotFound;
use LaravelModular\LaravelModular\LaravelModular;
use Modules\Billing\Events\InvoicePaid;
use Modules\Billing\Providers\BillingServiceProvider;
use Modules\Catalog\Events\OrderShipped;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Policies\ProductPolicy;
use Modules\Catalog\Providers\CatalogServiceProvider;

it('auto-discovers module providers with their full lifecycle', function () {
    expect(app()->getProvider(CatalogServiceProvider::class))->not->toBeNull()
        ->and(app()->getProvider(BillingServiceProvider::class))->not->toBeNull()
        ->and(app('catalog.booted_by_provider'))->toBeTrue();
});

it('auto-discovers module commands', function () {
    expect(Artisan::all())->toHaveKey('catalog:about');

    $this->artisan('catalog:about')
        ->expectsOutputToContain('Catalog module is active.')
        ->assertSuccessful();
});

it('auto-discovers module listeners from the handle type-hint', function () {
    expect(app('events')->hasListeners(OrderShipped::class))->toBeTrue()
        ->and(app('events')->hasListeners(InvoicePaid::class))->toBeTrue();
});

it('auto-discovers policies and observers', function () {
    expect(app(Gate::class)->policies())->toHaveKey(Product::class, ProductPolicy::class)
        ->and(app('events')->hasListeners('eloquent.created: '.Product::class))->toBeTrue();
});

it('loads module routes, views, translations, and config', function () {
    expect(Route::has('catalog.index'))->toBeTrue()
        ->and(Route::has('catalog.status'))->toBeTrue()
        ->and(Route::has('billing.index'))->toBeTrue()
        ->and(view()->exists('catalog::catalog'))->toBeTrue()
        ->and(trans('catalog::messages.name'))->toBe('Catalog')
        ->and(config('catalog.enabled'))->toBeTrue();
});

it('registers module migrations with the migrator', function () {
    $paths = array_map(
        fn (string $path): string => str_replace('\\', '/', $path),
        app('migrator')->paths(),
    );

    expect($paths)->toContain(str_replace('\\', '/', config('laravel-modular.path').'/Catalog/database/migrations'));
});

it('skips modules marked as disabled', function () {
    $modular = app(LaravelModular::class);

    expect($modular->has('Legacy'))->toBeFalse()
        ->and($modular->has('Catalog'))->toBeTrue()
        ->and($modular->moduleNames())->toContain('Billing')
        ->and(Route::has('legacy.index'))->toBeFalse();
});

it('resolves modules by name regardless of case', function () {
    expect(app(LaravelModular::class)->module('catalog')->name)->toBe('Catalog');
});

it('throws for unknown modules', function () {
    app(LaravelModular::class)->module('UnknownModule');
})->throws(ModuleNotFound::class);
