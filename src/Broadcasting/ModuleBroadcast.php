<?php

declare(strict_types=1);

namespace Laltu\Modular\Broadcasting;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Str;

/**
 * Module-level broadcasting helper.
 */
final class ModuleBroadcast
{
    use InteractsWithSockets;

    private ?string $module = null;

    public function __construct(?string $module = null)
    {
        $this->module = $module;
    }

    public function forModule(?string $module): static
    {
        $clone = clone $this;
        $clone->module = $module;
        return $clone;
    }

    public function event(object $event): void
    {
        $channel = $this->channelForEvent($event);
        event(new \Illuminate\Broadcasting\BroadcastEvent($event));
    }

    public function broadcast(object $event, ?string $channel = null): void
    {
        $channelName = $channel ?? $this->channelForEvent($event);
        broadcast($event, $channelName);
    }

    public function privateChannel(string $name): \Illuminate\Broadcasting\PrivateChannel
    {
        $channelName = $this->scopedName($name, 'private-');
        return new \Illuminate\Broadcasting\PrivateChannel($channelName);
    }

    public function presenceChannel(string $name): \Illuminate\Broadcasting\PresenceChannel
    {
        $channelName = $this->scopedName($name, 'presence-');
        return new \Illuminate\Broadcasting\PresenceChannel($channelName);
    }

    private function scopedName(string $name, string $prefix = ''): string
    {
        $scope = $this->module ? 'modular.'.$this->module.'.' : 'modular.';
        return $scope . $prefix . $name;
    }

    private function channelForEvent(object $event): string
    {
        $eventClass = get_class($event);
        $base = class_basename($eventClass);
        $scope = $this->module ? 'modular.'.$this->module : 'modular.global';
        return $scope . '.' . Str::kebab($base);
    }


}
