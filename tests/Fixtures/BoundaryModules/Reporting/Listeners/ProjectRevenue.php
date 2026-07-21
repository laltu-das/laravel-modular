<?php

declare(strict_types=1);

namespace Modules\Reporting\Listeners;

use Modules\Invoicing\Contracts\InvoiceGateway;
use Modules\Invoicing\Events\InvoiceIssued;

/**
 * Only uses the Invoicing module's public API (Contracts + Events), which is
 * always allowed by the boundary verifier.
 */
final class ProjectRevenue
{
    public function handle(InvoiceIssued $event, InvoiceGateway $gateway): void
    {
        //
    }
}
