<?php

declare(strict_types=1);

namespace Modules\Catalog\Listeners;

use Modules\Catalog\Events\OrderShipped;

final class SendShipmentEmail
{
    public function handle(OrderShipped $event): void
    {
        app()->instance('catalog.shipment_email', $event->trackingNumber);
    }
}
