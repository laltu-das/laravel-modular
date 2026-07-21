<?php

declare(strict_types=1);

namespace Modules\Catalog\Events;

final readonly class OrderShipped
{
    public function __construct(public string $trackingNumber)
    {
        //
    }
}
