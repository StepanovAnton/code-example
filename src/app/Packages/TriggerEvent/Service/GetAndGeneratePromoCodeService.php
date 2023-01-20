<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Service;

use App\Packages\PromoCode\Grpc\PromoCodeOperation;
use App\Packages\TriggerEvent\Dto\GetAndGeneratePromoCodeRequest;
use App\Packages\TriggerEvent\Dto\GetAndGeneratePromoCodeResponse;
use AssGuard\Contracts\v1\CheckExistsPromoActionRequest;
use AssGuard\Contracts\v1\GeneratePromoCodeRequest;
use AssGuard\Contracts\v1\GeneratePromoCodeResponse\Item;
use Spiral\RoadRunner\GRPC\Context;

class GetAndGeneratePromoCodeService
{
    private PromoCodeOperation $promoCodeOperation;

    public function __construct(PromoCodeOperation $promoCodeOperation)
    {
        $this->promoCodeOperation = $promoCodeOperation;
    }

    public function process(GetAndGeneratePromoCodeRequest $request): GetAndGeneratePromoCodeResponse
    {
        $promoCode = '';
        $actionId = $request->getActionId();
        $userId = (string)$request->getUserId();

        if (!$this->isExist($request->getActionId())) {
            return (new GetAndGeneratePromoCodeResponse())->setResult(false)->setMessage("Не найдена промоакция: $actionId");
        }

        $request = (new GeneratePromoCodeRequest())->setUserId([(string)$userId])->setPromoActionId($actionId);
        $responseGeneratePromoCode = $this->promoCodeOperation->generate(new Context([]), $request);

        /** @var Item $item */
        foreach ($responseGeneratePromoCode->getItem() as $item) {
            $promoCode = $item->getPromoCode();
        }

        if (empty($promoCode)) {
            return (new GetAndGeneratePromoCodeResponse())->setResult(false)->setMessage("Не удалось сгенерировать промокод по промоакция: $actionId");
        }

        return (new GetAndGeneratePromoCodeResponse())->setResult(true)->setPromoCode($promoCode);
    }

    public function isExist(string $promoActionCode): bool
    {
        return $this->promoCodeOperation->checkExistsPromoAction(
            new Context([]),
            (new CheckExistsPromoActionRequest())->setPromoActionId($promoActionCode)
        )->getResult();
    }
}
