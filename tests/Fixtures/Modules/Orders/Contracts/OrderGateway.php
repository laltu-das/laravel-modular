<?php

declare(strict_types=1);

namespace Modules\Orders\Contracts;

interface OrderGateway
{
    public function placeOrder(string $customerId, array $items): string;

    public function getOrder(string $orderId): ?array;

    public function cancelOrder(string $orderId): bool;
}