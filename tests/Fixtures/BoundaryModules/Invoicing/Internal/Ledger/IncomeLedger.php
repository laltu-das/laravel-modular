<?php

declare(strict_types=1);

namespace Modules\Invoicing\Internal\Ledger;

/**
 * Internal module class: other modules must not reference it.
 */
final class IncomeLedger
{
    public function record(int $amount): int
    {
        return $amount;
    }
}
