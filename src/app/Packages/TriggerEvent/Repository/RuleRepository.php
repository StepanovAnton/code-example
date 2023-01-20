<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Repository;

use App\Models\Rule;
use App\Packages\TriggerEvent\Dto\CreateRuleDTO;
use App\Packages\TriggerEvent\Dto\UpdateRuleDTO;
use App\Packages\TriggerEvent\Interfaces\RuleRepositoryInterface;
use Illuminate\Support\Collection;

class RuleRepository implements RuleRepositoryInterface
{
    public function getAll(): Collection
    {
        return Rule::all();
    }

    public function create(CreateRuleDTO $createRuleDTO): ?Rule
    {
        $rule = new Rule();

        $rule->title = $createRuleDTO->getTitle();
        $rule->template = $createRuleDTO->getTemplate();
        $rule->variables = $createRuleDTO->getVariables();

        return $rule->save() ? $rule : null;
    }

    public function update(UpdateRuleDTO $updateRuleDTO): ?Rule
    {
        $rule = $this->getById($updateRuleDTO->getId());

        $rule->title = $updateRuleDTO->getTitle();
        $rule->template = $updateRuleDTO->getTemplate();
        $rule->variables = $updateRuleDTO->getVariables();

        return $rule->save() ? $rule : null;
    }

    public function delete(int $id): ?bool
    {
        $rule = $this->getById($id);

        return $rule->delete();
    }

    public function getById(int $id): Rule
    {
        return Rule::query()->where('id', $id)->first();
    }
}
