<?php

declare(strict_types=1);

namespace Laltu\Modular\Testing;

final class ModuleFakeEvents
{
    private array $fakeEvents = [];

    public function register(string $eventClass): static
    {
        $this->fakeEvents[$eventClass] = true;
        \Illuminate\Support\Facades\Event::fake($eventClass);
        return $this;
    }

    public function unregister(string $eventClass): static
    {
        unset($this->fakeEvents[$eventClass]);
        return $this;
    }

    public function all(): array
    {
        return array_keys($this->fakeEvents);
    }
}
