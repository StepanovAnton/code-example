<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Repository;

use App\Models\Trigger;
use App\Packages\TriggerEvent\Dto\CreateTriggerDTO;
use App\Packages\TriggerEvent\Dto\UpdateTriggerDTO;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TriggerRepository
{
    /**
     * @param string $eventName
     * @return Collection
     */
    public function getActiveTriggersByEventName(string $eventName): Collection
    {
        return Trigger::query()->where('enabled', true)->where('event_name', $eventName)->get();
    }

    public function create(CreateTriggerDTO $createTriggerDTO): ?Trigger
    {
        $trigger = new Trigger();

        $trigger->title = $createTriggerDTO->getTitle();
        $trigger->event_name = $createTriggerDTO->getEventName();

        $trigger->save();

        foreach ($createTriggerDTO->getRuleIds() as $ruleId) {
            $trigger->rules()->attach($ruleId);
        }

        foreach ($createTriggerDTO->getActionIds() as $actionId) {
            $trigger->actions()->attach($actionId);
        }

        return $trigger->save() ? $trigger : null;
    }

    public function update(UpdateTriggerDTO $updateTriggerDTO): ?Trigger
    {
        $trigger = $this->getById($updateTriggerDTO->getId());

        $trigger->title = $updateTriggerDTO->getTitle();
        $trigger->event_name = $updateTriggerDTO->getEventName();
        $trigger->enabled = (int)$updateTriggerDTO->getEnabled();

        $trigger->rules()->detach();
        $trigger->actions()->detach();

        foreach ($updateTriggerDTO->getRuleIds() as $ruleId) {
            $trigger->rules()->attach($ruleId);
        }

        foreach ($updateTriggerDTO->getActionIds() as $actionId) {
            $trigger->actions()->attach($actionId);
        }

        return $trigger->save() ? $trigger : null;
    }

    public function delete(int $id): ?bool
    {
        /** @var Trigger $trigger */
        $trigger = $this->getById($id);

        return $trigger->delete();
    }

    public function getList(): \Illuminate\Database\Eloquent\Collection|array
    {
        return Trigger::all();
    }

    public function getById(int $id): Builder|Trigger
    {
        return Trigger::find($id);
    }
}
