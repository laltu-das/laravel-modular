<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use Laltu\Modular\Communication\Asynchronous\MessageBus;
use Laltu\Modular\Communication\Asynchronous\MessageJob;
use Modules\Inventory\Contracts\OrderPlaced;
use Modules\Inventory\Jobs\ReserveInventory;

beforeEach(function () {
    config(['laravel-modular.path' => __DIR__.'/../Fixtures/Modules']);
    Queue::fake();
});

it('lists the message consume command', function () {
    $this->artisan('message:consume --help')
        ->expectsOutputToContain('Consume messages and dispatch to registered handlers')
        ->assertSuccessful();
});

it('has correct options', function () {
    $this->artisan('message:consume --help')
        ->expectsOutputToContain('--connection')
        ->expectsOutputToContain('--queue')
        ->expectsOutputToContain('--timeout')
        ->expectsOutputToContain('--memory')
        ->expectsOutputToContain('--sleep')
        ->assertSuccessful();
});