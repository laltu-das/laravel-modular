<?php

declare(strict_types=1);

namespace Modules\Invoicing\Events;

final readonly class InvoiceIssued
{
    public function __construct(public int $amount)
    {
        //
    }
}
