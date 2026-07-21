<?php

declare(strict_types=1);

namespace Modules\Invoicing\Contracts;

interface InvoiceGateway
{
    public function charge(int $amount): bool;
}
