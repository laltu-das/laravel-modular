<?php

declare(strict_types=1);

namespace Laltu\Modular\Support;

final class ModuleLifecycleTracker
{
    private array $events = [];

    public function track(string $eventName, array $data): void
    {
        $this->events[] = array_merge($data, [
            'timestamp' => microtime(true),
            'event' => $eventName,
        ]);
    }

    public function timeline(): array
    {
        return $this->events;
    }

    public function duration(string $fromEvent, string $toEvent): ?float
    {
        $start = null;
        $end = null;

        foreach ($this->events as $event) {
            if ($event['event'] === $fromEvent) {
                $start = $event['timestamp'];
            }
            if ($event['event'] === $toEvent) {
                $end = $event['timestamp'];
            }
        }

        return ($start !== null && $end !== null) ? ($end - $start) * 1000 : null;
    }
}
