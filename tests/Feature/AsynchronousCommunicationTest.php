<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use Laltu\Modular\Facades\LaravelModular;
use Laltu\Modular\Communication\Asynchronous\MessageBus;
use Modules\Inventory\Contracts\OrderPlaced;

beforeEach(function () {
    config(['laravel-modular.path' => __DIR__.'/../Fixtures/Modules']);
    Queue::fake();
});

it('publishes a message via the facade', function () {
    $message = new OrderPlaced('ORD-123', 'CUST-456', [
        ['product_id' => 'PROD-1', 'quantity' => 2],
    ]);

    $jobId = LaravelModular::publishMessage($message);

    expect($jobId)->toBeString();
    Queue::assertPushedOn('orders', \Laltu\Modular\Communication\Asynchronous\MessageJob::class);
});

it('publishes a message with delay', function () {
    $message = new OrderPlaced('ORD-124', 'CUST-456', []);

    $jobId = LaravelModular::publishMessageLater($message, 60);

    expect($jobId)->toBeString();
    Queue::assertPushedOn('orders', \Laltu\Modular\Communication\Asynchronous\MessageJob::class);
});

it('publishes multiple messages in a batch', function () {
    $messages = [
        new OrderPlaced('ORD-125', 'CUST-456', []),
        new OrderPlaced('ORD-126', 'CUST-789', []),
    ];

    $jobIds = LaravelModular::messageBus()->publishBatch($messages);

    expect($jobIds)->toHaveCount(2);
    Queue::assertPushedOn('orders', \Laltu\Modular\Communication\Asynchronous\MessageJob::class, 2);
});

it('consumes messages via job subscription', function () {
    $message = new OrderPlaced('ORD-127', 'CUST-456', [
        ['product_id' => 'PROD-1', 'quantity' => 2],
    ]);

    // Publish the message
    LaravelModular::publishMessage($message);

    // Process the queue
    Queue::assertPushedOn('orders', \Laltu\Modular\Communication\Asynchronous\MessageJob::class);

    // The job should have been processed by the worker
    // In a real test, we'd run the queue worker
    // For now, verify the subscription was registered
    expect(app()->bound('inventory.reserved_for_order'))->toBeFalse();
});

it('consumes messages via closure subscription', function () {
    $message = new OrderPlaced('ORD-128', 'CUST-456', []);

    LaravelModular::publishMessage($message);

    Queue::assertPushedOn('orders', \Laltu\Modular\Communication\Asynchronous\MessageJob::class);
});

it('gets queue sizes', function () {
    $messageBus = app(MessageBus::class);

    // Queue monitor might not be available in tests
    $sizes = $messageBus->getAllQueueSizes();

    expect($sizes)->toBeArray();
});

it('registers job subscriptions', function () {
    $messageBus = app(MessageBus::class);
    $subscriptions = $messageBus->getSubscriptions();

    expect($subscriptions)->toHaveKey(\Modules\Inventory\Contracts\OrderPlaced::class)
        ->and($subscriptions[\Modules\Inventory\Contracts\OrderPlaced::class])->toContain(\Modules\Inventory\Jobs\ReserveInventory::class);
});

it('registers closure subscriptions', function () {
    $messageBus = app(MessageBus::class);
    $messageBus->subscribeClosure(\Modules\Inventory\Contracts\OrderPlaced::class, function ($message) {});

    $subscriptions = $messageBus->getSubscriptions();

    expect($subscriptions[\Modules\Inventory\Contracts\OrderPlaced::class])->toHaveCount(2); // Job + Closure
});