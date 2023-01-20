<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\RuleTemplates;

use App\Models\Event;
use App\Models\Trigger;
use App\Packages\TriggerEvent\Contract\Interfaces\OrderRepositoryInterface;
use App\Packages\TriggerEvent\Contract\OrderStatus;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\RuleTemplates\Interfaces\RuleInterface;

class OrderStatusRule extends AbstractRule implements RuleInterface
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
        $dateTime = null;

        if ($this->checkInTime) {
            $dateTime = $event->created_at->format('Y-m-d H:i:s');
        }

        $status = $this->ordersRepository->getOrderStatus($this->orderId, $dateTime);

        if ($status === $this->status) {
            return true;
        }

        $this->eventRepository->setLogMessage(
            $event,
            'Правило: '.self::getName().' Нужный статус - '.$this->status.'. Пришел статус - '.$status
        );

        return false;
    }

    public function getFieldsWithDefaults(): array
    {
        return [
            'status' => [
                'design' => 'Select',
                'required' => true,
                'title' => 'Статус',
                'placeholder' => 'Статус',
                'options' => OrderStatus::LIST,
            ],
            'checkInTime' => [
                'design' => 'Checkbox',
                'title' => 'Проверять статус по времени создания события?',
                'placeholder' => '',
                'required' => false,
            ],
        ];
    }

    public static function getName(): string
    {
        return 'order_status_rule';
    }

    public static function getLabel(): string
    {
        return 'Заказ в статусе';
    }
}
