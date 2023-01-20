<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\RuleTemplates;

use App\Models\Event;
use App\Models\Trigger;
use App\Packages\TriggerEvent\Dictionary\EventStatuses;
use App\Packages\TriggerEvent\Exception\EventStatusNotFind;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\RuleTemplates\Interfaces\RuleInterface;

class DelayMinutesRule extends AbstractRule implements RuleInterface
{
    private EventRepository $eventRepository;

    public int $delayMinutes;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @throws EventStatusNotFind
     */
    public function processRule(Event $event, Trigger $trigger): bool
    {
        if ($event->created_at->diffInRealMinutes() >= $this->delayMinutes) {
            return true;
        }

        $this->eventRepository->changeStatus($event, EventStatuses::DELAYED);
        $this->eventRepository->setDelayedUntil($event, $event->created_at->modify('+ '.$this->delayMinutes.' minutes'));

        return false;
    }

    public function getFieldsWithDefaults(): array
    {
        return [
            'delayMinutes' => [
                'design' => 'Input',
                'required' => true,
                'type' => 'number',
                'title' => 'Укажите минуты',
                'placeholder' => '',
            ],
        ];
    }

    public static function getName(): string
    {
        return 'delay_minutes';
    }

    public static function getLabel(): string
    {
        return 'Отправять через N минут';
    }
}
