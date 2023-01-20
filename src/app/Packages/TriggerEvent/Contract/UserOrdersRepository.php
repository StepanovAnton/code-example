<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Contract;

use App\Packages\Orders\Dto\UserOrdersCountDTO;
use App\Packages\Orders\Grpc\OrdersServiceGrpc;
use AssGuard\Contracts\v1\GetUserOrdersCountRequest;
use Exception;
use Spiral\RoadRunner\GRPC\Context;

class UserOrdersRepository implements Interfaces\UserOrdersRepositoryInterface
{
    private OrdersServiceGrpc $ordersClient;

    public function __construct(OrdersServiceGrpc $ordersClient)
    {
        $this->ordersClient = $ordersClient;
    }

    /**
     * @throws Exception
     */
    public function getUserOrdersCount(int $userId, string $dateFrom = null, string $dateTo = null): UserOrdersCountDTO
    {
        $request = (new GetUserOrdersCountRequest())->setUserId($userId);

        if ($dateFrom) {
            $request->setDateFrom($dateFrom);
        }

        if ($dateTo) {
            $request->setDateTo($dateTo);
        }

        $response = $this->ordersClient->getUserOrdersCount(new Context([]), $request);

        return new UserOrdersCountDTO($response->getTotal(), $response->getCanceled(), $response->getFinished(), $response->getDelivered());
    }
}
