<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Contract\Interfaces;

interface UserRepositoryInterface
{
    public function getTokensByBuyerId(int $externalId): array;

    public function isUserExistByExternalId(int $externalId): bool;

    public function getPhoneByKpp(int $externalId): int;
}
