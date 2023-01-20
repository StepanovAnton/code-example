<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\RuleTemplates;

use App\Models\Event;
use App\Models\Trigger;
use App\Packages\TriggerEvent\Contract\Interfaces\UserBasketInterface;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\RuleTemplates\Interfaces\RuleInterface;

class IsUserBasketEmptyRule extends AbstractRule implements RuleInterface
{
    private UserBasketInterface $userBasket;
    private EventRepository $eventRepository;
    public int $userId;

    public function __construct(
        UserBasketInterface $userBasket,
        EventRepository $eventRepository
    ) {
        $this->userBasket = $userBasket;
        $this->eventRepository = $eventRepository;
    }

    public function processRule(Event $event, Trigger $trigger): bool
    {
        $isUserBasketEmpty = $this->userBasket->isUserBasketEmpty($this->userId);

        if ($isUserBasketEmpty) {
            return true;
        }

        $this->eventRepository->setLogMessage($event, 'Правило: '.self::getName().'. У пользователя не пустая корзина');

        return false;
    }

    public function getFieldsWithDefaults(): array
    {
        return [];
    }

    public static function getName(): string
    {
        return 'is_user_basket_empty_rule';
    }

    public static function getLabel(): string
    {
        return 'У пользователя пустая корзина';
    }
}
