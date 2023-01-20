<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Dto;

class GetAndGeneratePromoCodeResponse
{
    public bool $result;
    public string $message;
    public string $promoCode;

    /**
     * @return bool
     */
    public function getResult(): bool
    {
        return $this->result;
    }

    /**
     * @param bool $result
     * @return GetAndGeneratePromoCodeResponse
     */
    public function setResult(bool $result): self
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return GetAndGeneratePromoCodeResponse
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getPromoCode(): string
    {
        return $this->promoCode;
    }

    /**
     * @param string $promoCode
     * @return GetAndGeneratePromoCodeResponse
     */
    public function setPromoCode(string $promoCode): self
    {
        $this->promoCode = $promoCode;

        return $this;
    }
}
