<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Contracts;

interface ModuleEventBus
{
    public function publish(object $event): object;
}
