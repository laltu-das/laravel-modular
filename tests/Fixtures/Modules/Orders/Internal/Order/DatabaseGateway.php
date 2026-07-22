<?php

declare(strict_types=1);

namespace Modules\Orders\Internal\Order;

use Modules\Orders\Contracts\OrderGateway;

final class DatabaseGateway implements OrderGateway
{
    private array $orders = [];

    public function placeOrder(string $customerId, array $items): string
    {
        $orderId = 'ORD-'.uniqid();
        $this->orders[$orderId] = [
            'id' => $orderId,
            'customer_id' => $customerId,
            'items' => $items,
            'status' => 'placed',
        ];

        return $orderId;
    }

    public function getOrder(string $orderId): ?array
    {
        return $this->orders[$orderId] ?? null;
    }

    public function cancelOrder(string $orderId): bool
    {
        if (! isset($this->orders[$orderId])) {
            return false;
        }

        $this->orders[$orderId]['status'] = 'cancelled';

        return true;
    }
}