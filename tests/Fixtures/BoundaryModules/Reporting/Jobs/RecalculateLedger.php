<?php

declare(strict_types=1);

namespace Modules\Reporting\Jobs;

use Modules\Invoicing\Internal\Ledger\IncomeLedger;

/**
 * Deliberate boundary violation: references another module's internal class.
 */
final class RecalculateLedger
{
    public function handle(IncomeLedger $ledger): int
    {
        return $ledger->record(10);
    }
}
