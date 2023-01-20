<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Jobs;

use App\Models\Event;
use App\Models\Trigger;
use App\Packages\TriggerEvent\Dictionary\EventStatuses;
use App\Packages\TriggerEvent\Exception\EventStatusNotFind;
use App\Packages\TriggerEvent\Exception\UnknownActionException;
use App\Packages\TriggerEvent\Exception\UnknownRuleException;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\Service\TriggerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ReflectionException;

class ProcessTriggerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Event $event;
    private Trigger $trigger;

    public function __construct(Event $event, Trigger $trigger)
    {
        $this->event = $event;
        $this->trigger = $trigger;
    }

    /**
     * @throws BindingResolutionException
     * @throws UnknownRuleException
     * @throws UnknownActionException
     * @throws EventStatusNotFind
     * @throws ReflectionException
     */
    public function handle()
    {
        $service = app()->make(TriggerService::class);
        $eventRepository = app()->make(EventRepository::class);

        $eventRepository->incrementAttempts($this->event);
        $result = $service->processTriggerRules($this->trigger, $this->event);

        if (!$result) {
            if ($this->event->status !== EventStatuses::DELAYED) {
                $eventRepository->changeStatus($this->event, EventStatuses::ERROR);
            }

            return true;
        }

        $resultAction = $service->processTriggerActions($this->trigger, $this->event);

        if ($resultAction === true) {
            $eventRepository->changeStatus($this->event, EventStatuses::PROCESSED);
        } else {
            $eventRepository->changeStatus($this->event, EventStatuses::ERROR);
        }

        return true;
    }
}
