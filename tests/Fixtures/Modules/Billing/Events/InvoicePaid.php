<?php

declare(strict_types=1);

namespace Modules\Billing\Events;

final readonly class InvoicePaid
{
    public function __construct(public int $amount)
    {
        //
    }
}
