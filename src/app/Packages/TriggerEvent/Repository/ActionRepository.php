<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Repository;

use App\Models\Action;
use App\Packages\TriggerEvent\Dto\CreateActionDTO;
use App\Packages\TriggerEvent\Dto\UpdateActionDTO;
use App\Packages\TriggerEvent\Interfaces\ActionRepositoryInterface;
use Illuminate\Support\Collection;

class ActionRepository implements ActionRepositoryInterface
{
    public function getAll(): Collection
    {
        return Action::all();
    }

    public function create(CreateActionDTO $createActionDTO): ?Action
    {
        $action = new Action();

        $action->title = $createActionDTO->getTitle();
        $action->template = $createActionDTO->getTemplate();
        $action->variables = $createActionDTO->getVariables();

        return $action->save() ? $action : null;
    }

    public function update(UpdateActionDTO $updateActionDTO): ?Action
    {
        $action = $this->getById($updateActionDTO->getId());

        $action->title = $updateActionDTO->getTitle();
        $action->template = $updateActionDTO->getTemplate();
        $action->variables = $updateActionDTO->getVariables();

        return $action->save() ? $action : null;
    }

    public function delete(int $id): ?bool
    {
        $action = $this->getById($id);

        return $action->delete();
    }

    public function getById(int $id): Action
    {
        return Action::query()->where('id', $id)->first();
    }
}
