<?php

declare(strict_types=1);

namespace Laltu\Modular\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware stack helper for module-level middleware.
 */
final class ApiModuleMiddleware
{
    /**
     * Handle an incoming request and attach module info.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $route = $request->route();
        $moduleParam = $route?->parameter('module');

        if (is_string($moduleParam)) {
            $request->attributes->set('module', $moduleParam);
            $request->attributes->set('api_version', 'v1');
            $request->headers->set('X-Module', $moduleParam);
        }

        return $next($request);
    }
}
