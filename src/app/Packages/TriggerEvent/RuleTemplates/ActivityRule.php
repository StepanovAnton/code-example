<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\RuleTemplates;

use App\Models\Event;
use App\Models\Trigger;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\RuleTemplates\Interfaces\RuleInterface;
use Carbon\Carbon;

class ActivityRule extends AbstractRule implements RuleInterface
{
    private EventRepository $eventRepository;

    public string $dateFrom;
    public string $dateTo;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function processRule(Event $event, Trigger $trigger): bool
    {
        $dateFrom = Carbon::createFromFormat('Y-m-d', $this->dateFrom);
        $dateTo = Carbon::createFromFormat('Y-m-d', $this->dateTo);

        $now = Carbon::now();

        if (!$now->between($dateFrom, $dateTo)) {
            $now = $now->format('Y-m-d');
            $dateFrom = $dateFrom->format('Y-m-d');
            $dateTo = $dateTo->format('Y-m-d');
            $this->eventRepository->setLogMessage($event, "Правило: ActivityRule. Дата от - $dateFrom, Дата по $dateTo, Текущая дата $now не попадает в временной промежуток");

            return false;
        }

        return true;
    }

    public function getFieldsWithDefaults(): array
    {
        return [
            'dateFrom' => [
                'design' => 'DateTimer',
                'required' => true,
                'title' => 'Укажите с какого числа активна',
                'placeholder' => '',
                'format' => 'Y-m-d',
            ],
            'dateTo' => [
                'design' => 'DateTimer',
                'required' => true,
                'title' => 'Укажите по какое число активна',
                'placeholder' => '',
                'format' => 'Y-m-d',
            ],
        ];
    }

    public static function getName(): string
    {
        return 'activity';
    }

    public static function getLabel(): string
    {
        return 'Активность';
    }
}
