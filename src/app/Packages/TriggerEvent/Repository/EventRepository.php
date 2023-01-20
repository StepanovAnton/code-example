<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Repository;

use App\Models\Event;
use App\Packages\TriggerEvent\Dictionary\EventStatuses;
use App\Packages\TriggerEvent\Exception\EventStatusNotFind;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EventRepository
{
    public function create(string $name): ?Event
    {
        $event = new Event();
        $event->name = $name;

        return $event->save() ? $event : null;
    }

    public function getCurrentEvents(): array|Collection
    {
        return Event::where('status', EventStatuses::NEW)
            ->orWhere(function (Builder $query) {
                $query->where('status', EventStatuses::DELAYED)
                    ->where('delayed_until', '<=', Carbon::now());
            })
            ->orderBy('events.id', 'ASC')
            ->get();
    }

    /**
     * @throws EventStatusNotFind
     */
    public function changeStatus(Event $event, int $status): ?Event
    {
        if (!isset(EventStatuses::LIST[$status])) {
            throw new EventStatusNotFind();
        }

        $event->status = $status;

        return $event->save() ? $event : null;
    }

    public function incrementAttempts(Event $event): ?Event
    {
        $event->attempts = $event->attempts + 1;

        return $event->save() ? $event : null;
    }

    public function setLogMessage(Event $event, string $logMessage): ?Event
    {
        $event->log_message = $logMessage;

        return $event->save() ? $event : null;
    }

    public function getById(int $id): Event
    {
        return Event::find($id);
    }

    public function isSuccessEventExistByExternalUserId(Collection $payloads, string $eventName, int $currentEventId): bool
    {
        $query = Event::where('name', $eventName)
            ->whereIn('status', [EventStatuses::PROCESSED, EventStatuses::CHECKED]);

        foreach ($payloads as $payload) {
            $query->whereHas('payloads', function (Builder $builder) use ($payload) {
                $builder
                    ->where('object_type', $payload->object_type)
                    ->where('object_value', $payload->object_value);
            });
        }

        $res = $query->orderBy('events.id', 'ASC')->get();

        if ($res->count() === 0) {
            return false;
        }

        if ($res->where('status', 3)->count()) {
            return true;
        }

        if ($res->where('status', 1)->count()) {
            $ids = $res->where('status', 1)->pluck('id');
            $firstId = $ids->first();

            if ($firstId < $currentEventId) {
                return true;
            }
        }

        return false;
    }

    public function setDelayedUntil(Event $event, \Illuminate\Support\Carbon $delayedUntil): ?Event
    {
        $event->delayed_until = $delayedUntil;

        return $event->save() ? $event : null;
    }
}
