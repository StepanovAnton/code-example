<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\RuleTemplates;

use App\Models\Event;
use App\Models\Trigger;
use App\Packages\TriggerEvent\Contract\Interfaces\UserOrdersRepositoryInterface;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\RuleTemplates\Interfaces\RuleInterface;

class OrderCountRule extends AbstractRule implements RuleInterface
{
    private UserOrdersRepositoryInterface $userOrdersRepository;
    private EventRepository $eventRepository;

    public int $ordersCount;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public int $userId = 0;

    public function __construct(
        UserOrdersRepositoryInterface $userOrdersRepository,
        EventRepository $eventRepository
    ) {
        $this->userOrdersRepository = $userOrdersRepository;
        $this->eventRepository = $eventRepository;
    }

    public function processRule(Event $event, Trigger $trigger): bool
    {
        if ($this->userId === 0) {
            $this->eventRepository->setLogMessage($event, 'Правило: '.self::getName().'. Не указан userId');

            return false;
        }

        $dateTo = $this->dateTo ?: $event->created_at->format('Y-m-d H:i:s');

        $response = $this->userOrdersRepository->getUserOrdersCount($this->userId, $this->dateFrom, $dateTo);

        $finishedOrdersCount = $response->getFinishedOrdersCount();
        $deliveredOrdersCount = $response->getDeliveredOrderCount();

        $realUserOrderCount = $deliveredOrdersCount + $finishedOrdersCount;

        $needleUserOrderCount = $this->ordersCount;

        if ($needleUserOrderCount === $realUserOrderCount) {
            return true;
        }

        $this->eventRepository->setLogMessage($event, 'Правило: '.self::getName().". Нужное количество заказов - $needleUserOrderCount, Реально количество заказов - $realUserOrderCount");

        return false;
    }

    public function getFieldsWithDefaults(): array
    {
        return [
            'ordersCount' => [
                'design' => 'Input',
                'required' => true,
                'type' => 'number',
                'title' => 'Количество заказов',
                'placeholder' => 'Количество заказов',
            ],
            'dateFrom' => [
                'design' => 'DateTimer',
                'required' => false,
                'title' => 'С какого числа получить заказы',
                'format' => 'Y-m-d',
            ],
            'dateTo' => [
                'design' => 'DateTimer',
                'required' => false,
                'title' => 'По какое число получить заказы',
                'format' => 'Y-m-d',
            ],
        ];
    }

    public static function getName(): string
    {
        return 'order_count_rule';
    }

    public static function getLabel(): string
    {
        return 'Количество заказов в статусе отличном от «отменен»';
    }
}
