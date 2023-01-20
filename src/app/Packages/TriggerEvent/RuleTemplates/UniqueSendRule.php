<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\RuleTemplates;

use App\Models\Event;
use App\Models\Trigger;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\RuleTemplates\Interfaces\RuleInterface;

class UniqueSendRule extends AbstractRule implements RuleInterface
{
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function processRule(Event $event, Trigger $trigger): bool
    {
        if ($this->eventRepository->isSuccessEventExistByExternalUserId($event->payloads, $event->name, $event->id)) {
            $this->eventRepository->setLogMessage($event, 'Правило: '.self::getName().'. Уже отправлялось пользователю');

            return false;
        }

        return true;
    }

    public function getFieldsWithDefaults(): array
    {
        return [];
    }

    public static function getName(): string
    {
        return 'unique_send';
    }

    public static function getLabel(): string
    {
        return 'Триггер должен успешно сработать только на 1 пользователя';
    }
}
