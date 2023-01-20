<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\RuleTemplates;

use App\Models\Event;
use App\Models\Trigger;
use App\Packages\TriggerEvent\Contract\Interfaces\OrderRepositoryInterface;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\RuleTemplates\Interfaces\RuleInterface;

class OrderIsRatedRule extends AbstractRule implements RuleInterface
{
    private OrderRepositoryInterface $ordersRepository;
    private EventRepository $eventRepository;

    public int $status;
    public string $orderId;
    public bool $checkInTime = false;

    public function __construct(
        OrderRepositoryInterface $ordersRepository,
        EventRepository $eventRepository
    ) {
        $this->ordersRepository = $ordersRepository;
        $this->eventRepository = $eventRepository;
    }

    public function processRule(Event $event, Trigger $trigger): bool
    {
        $isRated = $this->ordersRepository->isOrderRated($this->orderId);

        if ($isRated === false) {
            return true;
        }

        $this->eventRepository->setLogMessage(
            $event,
            'Правило: '.self::getName().' Заказ оценен'
        );

        return false;
    }

    public function getFieldsWithDefaults(): array
    {
        return [];
    }

    public static function getName(): string
    {
        return 'order_is_rated_rule';
    }

    public static function getLabel(): string
    {
        return 'Заказ не оценен';
    }
}
