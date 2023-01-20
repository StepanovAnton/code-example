<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\RuleTemplates;

use App\Models\Event;
use App\Models\Trigger;
use App\Packages\TriggerEvent\Contract\Interfaces\OrderRepositoryInterface;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\RuleTemplates\Interfaces\RuleInterface;

class IsPayUrlExistRule extends AbstractRule implements RuleInterface
{
    private OrderRepositoryInterface $ordersRepository;
    private EventRepository $eventRepository;

    public string $masterOrderId;

    public function __construct(
        OrderRepositoryInterface $ordersRepository,
        EventRepository $eventRepository
    ) {
        $this->ordersRepository = $ordersRepository;
        $this->eventRepository = $eventRepository;
    }

    public function processRule(Event $event, Trigger $trigger): bool
    {
        if ($this->ordersRepository->getPayUrlCollectClickAndCollect($this->masterOrderId)) {
            return true;
        }

        $this->eventRepository->setLogMessage(
            $event, 'Правило: '.self::getName().' не вернулась ссылка на оплату'
        );

        return false;
    }

    public function getFieldsWithDefaults(): array
    {
        return [];
    }

    public static function getName(): string
    {
        return 'is_pay_url_exist_rule';
    }

    public static function getLabel(): string
    {
        return 'Проверяет существует ли ссылка на оплату';
    }
}
