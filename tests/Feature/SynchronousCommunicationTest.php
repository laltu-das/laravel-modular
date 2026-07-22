<?php

declare(strict_types=1);

use Laltu\Modular\Facades\LaravelModular;
use Laltu\Modular\LaravelModular as LaravelModularClass;
use Modules\Orders\Contracts\OrderGateway;

beforeEach(function () {
    config(['laravel-modular.path' => __DIR__.'/../Fixtures/Modules']);
});

it('resolves a module public API via the facade', function () {
    $gateway = LaravelModular::api(OrderGateway::class);

    expect($gateway)->toBeInstanceOf(OrderGateway::class);
});

it('calls methods on the resolved API (synchronous communication)', function () {
    $gateway = LaravelModular::api(OrderGateway::class);

    $orderId = $gateway->placeOrder('CUST-123', [
        ['product_id' => 'PROD-1', 'quantity' => 2],
        ['product_id' => 'PROD-2', 'quantity' => 1],
    ]);

    expect($orderId)->toStartWith('ORD-');

    $order = $gateway->getOrder($orderId);

    expect($order)->not->toBeNull()
        ->and($order['customer_id'])->toBe('CUST-123')
        ->and($order['status'])->toBe('placed');
});

it('resolves API from a specific module', function () {
    $gateway = LaravelModular::apiFrom(OrderGateway::class, 'Orders');

    expect($gateway)->toBeInstanceOf(OrderGateway::class);
});

it('checks if an API is available', function () {
    expect(LaravelModular::hasApi(OrderGateway::class))->toBeTrue();
    expect(LaravelModular::hasApi('NonExistent\Interface'))->toBeFalse();
});

it('lists all public APIs across modules', function () {
    $apis = LaravelModular::allApis();

    expect($apis)->toHaveKey('Orders')
        ->and($apis['Orders'])->toHaveKey(OrderGateway::class);
});

it('finds which module provides an interface', function () {
    $module = LaravelModular::getProviderModule(OrderGateway::class);

    expect($module)->toBe('Orders');
});

it('throws when resolving from wrong module', function () {
    LaravelModular::apiFrom(OrderGateway::class, 'Inventory')
        ->toThrow(\InvalidArgumentException::class);
});