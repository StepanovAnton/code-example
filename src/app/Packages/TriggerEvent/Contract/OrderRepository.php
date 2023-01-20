<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Contract;

use App\Packages\Orders\Contracts\OrdersInterface;
use App\Packages\TriggerEvent\Contract\Interfaces\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    private OrdersInterface $orders;

    public function __construct(OrdersInterface $orders)
    {
        $this->orders = $orders;
    }

    public function getPayUrlCollectClickAndCollect(string $masterOrderId): bool
    {
        return $this->orders->getPayUrlCollectClickAndCollect($masterOrderId);
    }

    public function getOrderStatus(string $orderId, ?string $dateTime): int
    {
        return $this->orders->getOrderStatus($orderId, $dateTime);
    }

    public function getOrderPrice(string $orderId): int
    {
        return $this->orders->getOrderPrice($orderId);
    }

    public function isOrderRated(string $orderId): bool
    {
        return $this->orders->isOrderRated($orderId);
    }
}
