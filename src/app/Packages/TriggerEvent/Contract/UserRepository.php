<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Contract;

use App\Packages\TriggerEvent\Contract\Interfaces\UserRepositoryInterface;
use App\Packages\User\Grpc\UserService;
use AssGuard\Contracts\v1\GetPhoneByKppRequest;
use AssGuard\Contracts\v1\GetTokensByBuyerIdsRequest;
use AssGuard\Contracts\v1\GetTokensByBuyerIdsResponse\User\Token;
use AssGuard\Contracts\v1\UserExistsRequest;
use Spiral\RoadRunner\GRPC\Context;

class UserRepository implements UserRepositoryInterface
{
    private UserService $userGrpcClient;

    public function __construct(UserService $userGrpcClient)
    {
        $this->userGrpcClient = $userGrpcClient;
    }

    public function getTokensByBuyerId(int $externalId): array
    {
        $userTokens = [];

        $response = $this->userGrpcClient->getTokensByBuyerIds(
            new Context([]),
            (new GetTokensByBuyerIdsRequest())->setBuyerIds([(string)$externalId]),
        );
        if ($response->getUsers()->count() > 0) {
            /** @var Token $userToken */
            foreach ($response->getUsers()[0]->getTokens() as $userToken) {
                $userTokens[] =
                    [
                        'token' => $userToken->getToken(),
                        'platform' => $userToken->getTokenPlatform(),
                        'type' => $userToken->getTokenType(),
                        'version' => $userToken->getVersion(),
                    ];
            }
        }

        return $userTokens;
    }

    public function isUserExistByExternalId(int $externalId): bool
    {
        $response = $this->userGrpcClient->isUsersExists(
            new Context([]),
            (new UserExistsRequest())->setValue([(string)$externalId])->setType(UserExistsRequest\Type::KPP),
        );

        $result = [];
        foreach ($response->getResult() as $item) {
            $result[] = $item;
        }

        return in_array($externalId, $result);
    }

    public function getPhoneByKpp(int $externalId): int
    {
        $response = $this->userGrpcClient->getPhoneByKpp(
            new Context([]),
            (new GetPhoneByKppRequest())->setKpp($externalId),
        );

        return $response->getPhone();
    }
}
