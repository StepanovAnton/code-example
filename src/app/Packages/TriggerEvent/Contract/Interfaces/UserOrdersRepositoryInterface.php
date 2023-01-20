<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Contract\Interfaces;

use App\Packages\Orders\Dto\UserOrdersCountDTO;

interface UserOrdersRepositoryInterface
{
    public function getUserOrdersCount(int $userId, string $dateFrom = null, string $dateTo = null): UserOrdersCountDTO;
}
