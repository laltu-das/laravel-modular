<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Discovery;

use Laltu\LaravelModular\Support\Module;
use ReflectionClass;
use ReflectionNamedType;

/**
 * Wires module listeners like Laravel's native event discovery: the event a
 * listener handles is inferred from the type-hint of its handle method.
 */
final readonly class ListenerDiscovery
{
    public function __construct(private ModuleClassDiscovery $classes)
    {
        //
    }

    /**
     * Map module listener classes to the event declared by their handle method.
     *
     * @return array<string, list<string>>
     */
    public function discover(Module $module): array
    {
        $listeners = [];

        foreach ($this->classes->concreteIn($module, 'Listeners') as $listener) {
            $event = $this->listensTo($listener);

            if ($event === null) {
                continue;
            }

            $listeners[$event][] = $listener;
        }

        return $listeners;
    }

    /** @param  class-string  $listener */
    private function listensTo(string $listener): ?string
    {
        $reflection = new ReflectionClass($listener);

        foreach (['handle', '__invoke'] as $method) {
            if (! $reflection->hasMethod($method)) {
                continue;
            }

            $type = ($reflection->getMethod($method)->getParameters()[0] ?? null)?->getType();

            if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                return $type->getName();
            }
        }

        return null;
    }
}
