<?php

declare(strict_types=1);

use Laltu\Modular\LaravelModular;
use Modules\Billing\Events\InvoicePaid;
use Modules\Catalog\Events\OrderShipped;

it('wires module listeners automatically from their handle type-hint', function () {
    app(Laltu\Modular::class)->publish(new OrderShipped('TRK-42'));

    expect(app('catalog.shipment_email'))->toBe('TRK-42');
});

it('lets modules communicate through events without referencing each other', function () {
    $responses = app(Laltu\Modular::class)->publish(new InvoicePaid(250));

    expect($responses)->toBeArray()
        ->and(app('catalog.released_invoice_amount'))->toBe(250);
});

it('subscribes runtime listeners, including wildcards', function () {
    $modular = app(Laltu\Modular::class);

    $modular->listen(OrderShipped::class, function (OrderShipped $event): void {
        app()->instance('catalog.runtime_listener', $event->trackingNumber);
    });

    $modular->listen('Modules\\Billing\\Events\\*', function (InvoicePaid $event): void {
        app()->instance('catalog.wildcard_listener', $event->amount);
    });

    $modular->publish(new OrderShipped('TRK-7'));
    $modular->publish(new InvoicePaid(90));

    expect(app('catalog.runtime_listener'))->toBe('TRK-7')
        ->and(app('catalog.wildcard_listener'))->toBe(90);
});
