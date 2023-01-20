<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Dto;

class SenderDTO
{
    private ?string $promoCode = null;
    private ?int $creatorId = null;
    private ?string $title = null;
    private ?string $body = null;
    private ?string $link = null;
    private ?int $type = null;
    private ?string $identifier = null;
    private ?array $data = null;
    private ?array $appends = null;
    private ?int $platform = null;
    private ?string $version = null;

    public function getPromoCode(): ?string
    {
        return $this->promoCode;
    }

    public function setPromoCode(?string $promoCode): self
    {
        $this->promoCode = $promoCode;

        return $this;
    }

    public function getCreatorId(): ?int
    {
        return $this->creatorId;
    }

    public function setCreatorId(?int $creatorId): self
    {
        $this->creatorId = $creatorId;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getAppends(): ?array
    {
        return $this->appends;
    }

    public function setAppends(?array $appends): self
    {
        $this->appends = $appends;

        return $this;
    }

    public function setPlatform(?int $platform): self
    {
        $this->platform = $platform;

        return $this;
    }

    public function getPlatform(): ?int
    {
        return $this->platform;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }
}
