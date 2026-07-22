<?php

declare(strict_types=1);

namespace Laltu\Modular\Inertia;

use Closure;
use Illuminate\Http\Request;

/**
 * Inertia middleware for module-aware rendering.
 * Handles X-Inertia, X-Inertia-Version, and partial reloads.
 */
final class InertiaMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (class_exists(\Inertia\Inertia::class)) {
            \Inertia\Inertia::setRootView('app');
        }

        $request->attributes->set('inertia', true);

        $response = $next($request);

        if (! $request->header('X-Inertia')) {
            return $response;
        }

        if ($request->header('X-Inertia-Version') !== config('inertia.version', '')) {
            return redirect()->back()->with('inertia_location', $request->url());
        }

        return $response;
    }
}
