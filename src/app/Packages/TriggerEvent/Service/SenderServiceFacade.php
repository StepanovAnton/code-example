<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Service;

use App\Packages\Sender\Grpc\SenderService;
use App\Packages\TriggerEvent\Convert\SenderDTOToGrpcRequest;
use App\Packages\TriggerEvent\Dto\SenderDTO;
use AssGuard\Contracts\v1\SendResponse;
use Spiral\RoadRunner\GRPC\Context;

class SenderServiceFacade
{
    private SenderService $senderService;
    private SenderDTOToGrpcRequest $senderDTOToGrpcRequest;

    public function __construct(
        SenderService $senderService,
        SenderDTOToGrpcRequest $senderDTOToGrpcRequest,
    ) {
        $this->senderService = $senderService;
        $this->senderDTOToGrpcRequest = $senderDTOToGrpcRequest;
    }

    public function sendPrivateMessage(SenderDTO $senderDTO): SendResponse
    {
        $grpcRequest = $this->senderDTOToGrpcRequest->convert($senderDTO);

        return $this->senderService->private(new Context([]), $grpcRequest);
    }

    public function sendFirebase(SenderDTO $senderDTO): SendResponse
    {
        $grpcRequest = $this->senderDTOToGrpcRequest->convert($senderDTO);

        return $this->senderService->firebase(new Context([]), $grpcRequest);
    }

    public function sendSMS(SenderDTO $senderDTO): SendResponse
    {
        $grpcRequest = $this->senderDTOToGrpcRequest->convert($senderDTO);

        return $this->senderService->sms(new Context([]), $grpcRequest);
    }
}
