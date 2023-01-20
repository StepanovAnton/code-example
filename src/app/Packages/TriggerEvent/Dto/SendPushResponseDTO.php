<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Dto;

class SendPushResponseDTO
{
    private int $isSent;
    private ?string $token;
    private ?string $pushId;

    public function setIsSent(int $isSent): self
    {
        $this->isSent = $isSent;

        return $this;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function setPushId(?string $pushId): self
    {
        $this->pushId = $pushId;

        return $this;
    }

    public function getIsSent(): int
    {
        return $this->isSent;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getPushId(): ?string
    {
        return $this->pushId;
    }
}
