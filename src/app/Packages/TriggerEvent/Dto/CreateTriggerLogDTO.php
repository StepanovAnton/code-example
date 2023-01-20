<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Dto;

class CreateTriggerLogDTO
{
    private int $externalUserId;
    private ?string $promoCode;
    private int $triggerId;
    private int $type;
    private string $message;
    private int $actionId;
    private string $actionTemplate;
    private int $sent;

    public function __construct(
        int $triggerId,
        ?string $promoCode,
        int $externalUserId,
        int $type,
        string $message,
        int $actionId,
        string $actionTemplate,
        int $sent
    ) {
        $this->triggerId = $triggerId;
        $this->promoCode = $promoCode;
        $this->externalUserId = $externalUserId;
        $this->type = $type;
        $this->message = $message;
        $this->actionId = $actionId;
        $this->actionTemplate = $actionTemplate;
        $this->sent = $sent;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getExternalUserId(): int
    {
        return $this->externalUserId;
    }

    public function getPromoCode(): ?string
    {
        return $this->promoCode;
    }

    public function getTriggerId(): int
    {
        return $this->triggerId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getActionId(): int
    {
        return $this->actionId;
    }

    public function getActionTemplate(): string
    {
        return $this->actionTemplate;
    }

    public function getSent(): int
    {
        return $this->sent;
    }
}
