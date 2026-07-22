<?php

declare(strict_types=1);

namespace Laltu\Modular\Contracts;

use Laltu\Modular\Support\Module;

/**
 * Decides whether a module is active for the current tenant.
 *
 * Think of it as Spring's per-profile component filtering: the module stays on
 * disk and autoloadable, but it is only booted (providers, routes, listeners,
 * commands, etc.) when the voter allows it for the resolved tenant.
 */
interface TenantModuleVoter
{
    public function allows(Module $module, mixed $tenant): bool;
}
