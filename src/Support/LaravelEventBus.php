<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Support;

use Illuminate\Contracts\Events\Dispatcher;
use LaravelModular\LaravelModular\Contracts\ModuleEventBus;

final readonly class LaravelEventBus implements ModuleEventBus
{
    public function __construct(private Dispatcher $events) {}

    public function publish(object $event): object
    {
        $this->events->dispatch($event);

        return $event;
    }
}
