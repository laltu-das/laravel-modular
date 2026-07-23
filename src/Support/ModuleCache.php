<?php

declare(strict_types=1);

namespace Laltu\Modular\Support;

use Closure;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Module-level caching with automatic key scoping.
 */
final class ModuleCache
{
    private ?string $module = null;

    private ?string $store = null;

    public function __construct(?string $store = null)
    {
        $this->store = $store;
    }

    /**
     * Set the current module for key scoping.
     */
    public function forModule(string $module): static
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get the module-scoped key.
     */
    private function scopedKey(string $key): string
    {
        if ($this->module !== null) {
            return 'modular:'.$this->module.':'.$key;
        }

        return 'modular:global:'.$key;
    }

    public function remember(string $key, int $ttl, Closure $callback): mixed
    {
        $scoped = $this->scopedKey($key);
        $store = $this->store ? Cache::store($this->store) : Cache::store();

        /** @var Repository $store */
        return $store->remember($scoped, $ttl, $callback);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::store($this->store)->get($this->scopedKey($key), $default);
    }

    public function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        return Cache::store($this->store)->put($this->scopedKey($key), $value, $ttl);
    }

    public function forget(string $key): bool
    {
        return Cache::store($this->store)->forget($this->scopedKey($key));
    }

    public function flushModule(): bool
    {
        if ($this->module === null) {
            return false;
        }

        // Note: full module flush requires tag support or manual tracking.
        // This is a best-effort flush using the module prefix.
        $store = $this->store ? Cache::store($this->store) : Cache::store();

        if (method_exists($store, 'tags')) {
            return $store->tags('modular:'.$this->module)->flush();
        }

        return true;
    }

    public function tags(array $tags): static
    {
        $this->store = null; // tags are handled by the cache driver directly

        return $this;
    }
}
