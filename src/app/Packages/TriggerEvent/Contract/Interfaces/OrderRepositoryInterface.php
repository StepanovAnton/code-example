<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Contract\Interfaces;

interface OrderRepositoryInterface
{
    public function getPayUrlCollectClickAndCollect(string $masterOrderId): bool;

    public function getOrderStatus(string $orderId, ?string $dateTime): int;

    public function getOrderPrice(string $orderId): int;

    public function isOrderRated(string $orderId): bool;
}
