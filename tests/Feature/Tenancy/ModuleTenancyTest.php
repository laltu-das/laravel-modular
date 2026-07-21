<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelModular\LaravelModular\LaravelModular;
use LaravelModular\LaravelModular\Support\CurrentTenant;

it('resolves the current tenant through the configured resolver', function () {
    expect(app(LaravelModular::class)->tenant())->toBe(123)
        ->and(app(CurrentTenant::class)->get())->toBe(123)
        ->and(app(CurrentTenant::class)->has())->toBeTrue();
});

it('only boots modules the tenant voter allows', function () {
    $modular = app(LaravelModular::class);

    expect($modular->moduleNames())->toContain('Catalog')
        ->and($modular->moduleNames())->not->toContain('Billing')
        ->and($modular->has('Billing'))->toBeFalse()
        ->and($modular->isEnabled($modular->module('Catalog')))->toBeTrue()
        ->and(Route::has('catalog.index'))->toBeTrue()
        ->and(Route::has('billing.index'))->toBeFalse()
        ->and(app()->bound('billing.registered'))->toBeFalse()
        ->and(app()->bound('catalog.booted_by_provider'))->toBeTrue();
});
