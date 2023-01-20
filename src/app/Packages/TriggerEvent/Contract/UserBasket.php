<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Contract;

use App\Packages\Basket\Grpc\BasketServiceGrpc;
use App\Packages\TriggerEvent\Contract\Interfaces\UserBasketInterface;
use AssGuard\Contracts\v1\IsUserBasketEmptyRequest;

class UserBasket implements UserBasketInterface
{
    private BasketServiceGrpc $basketClient;

    public function __construct(BasketServiceGrpc $basketClient)
    {
        $this->basketClient = $basketClient;
    }

    public function isUserBasketEmpty(int $userId): bool
    {
        $request = (new IsUserBasketEmptyRequest())->setUserId($userId);

        $response = $this->basketClient->isUserBasketEmpty($request, []);

        return $response->getIsEmpty();
    }
}
