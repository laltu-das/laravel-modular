<?php

declare(strict_types=1);

namespace Modules\Catalog\Listeners;

use Modules\Billing\Events\InvoicePaid;

/**
 * Cross-module, event-driven communication: Catalog reacts to a Billing event
 * without Billing knowing anything about Catalog.
 */
final class ReleaseInventory
{
    public function handle(InvoicePaid $event): void
    {
        app()->instance('catalog.released_invoice_amount', $event->amount);
    }
}
