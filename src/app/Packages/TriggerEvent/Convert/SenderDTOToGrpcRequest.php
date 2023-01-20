<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Convert;

use App\Packages\Sender\Converter\DataMessageConverter;
use App\Packages\TriggerEvent\Dto\SenderDTO;
use AssGuard\Contracts\v1\SendRequest;
use AssGuard\Contracts\v1\SendRequest\Append;

class SenderDTOToGrpcRequest
{
    private const EXTERNAL_ID = 95001499;
    private DataMessageConverter $converter;

    public function __construct(DataMessageConverter $converter)
    {
        $this->converter = $converter;
    }

    public function convert(SenderDTO $senderDTO): SendRequest
    {
        $sendRequest = (new SendRequest())
            ->setIdentifier([$senderDTO->getIdentifier()])
            ->setCreatorId(self::EXTERNAL_ID);

        if ($senderDTO->getPlatform()) {
            $sendRequest->setPlatform($senderDTO->getPlatform());
        }

        if ($senderDTO->getVersion()) {
            $sendRequest->setVersion($senderDTO->getVersion());
        }

        if ($senderDTO->getData()) {
            $data = $this->converter->convertToGrpc($senderDTO->getData());

            $sendRequest->setData($data);
        }

        if ($senderDTO->getPromoCode()) {
            $sendRequest->setPromoCode($senderDTO->getPromoCode());
        }

        if ($senderDTO->getTitle()) {
            $sendRequest->setTitle($senderDTO->getTitle());
        }

        if ($senderDTO->getBody()) {
            $sendRequest->setBody($senderDTO->getBody());
        }

        if ($senderDTO->getType()) {
            $sendRequest->setType($senderDTO->getType());
        }

        if ($senderDTO->getLink()) {
            $sendRequest->setLink($senderDTO->getLink());
        }

//        if ($senderDTO->getAppends() && !empty($senderDTO->getAppends())) { //todo выяснить и переделать
        if ($senderDTO->getAppends()) {
            $appends = [];

            foreach ($senderDTO->getAppends() as $key => $value) {
                $appends[] = (new Append)->setKey($key)->setValue($value);
            }

            $sendRequest->setAppends($appends);
        }

        return $sendRequest;
    }
}
