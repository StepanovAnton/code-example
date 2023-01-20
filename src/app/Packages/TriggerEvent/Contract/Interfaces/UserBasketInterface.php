<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Contract\Interfaces;

interface UserBasketInterface
{
    public function isUserBasketEmpty(int $userId): bool;
}
