<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Dto;

class GetAndGeneratePromoCodeRequest
{
    private string $actionId;
    private int $userId;

    public function __construct(string $actionId, int $userId)
    {
        $this->actionId = $actionId;
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getActionId(): string
    {
        return $this->actionId;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }
}
