<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Service;

class GetAuditoryForABService
{
    public function calculateValue(int $userId, string $salt): int
    {
        return hexdec(substr(md5($userId.$salt), 1, 12)) % 10;
    }
}
