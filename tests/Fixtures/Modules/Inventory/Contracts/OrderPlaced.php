<?php

declare(strict_types=1);

namespace Modules\Inventory\Contracts;

use Laltu\Modular\Communication\Asynchronous\BaseMessage;

final readonly class OrderPlaced extends BaseMessage
{
    public function __construct(
        public string $orderId,
        public string $customerId,
        public array $items,
    ) {}

    public function channel(): string
    {
        return 'orders';
    }
}