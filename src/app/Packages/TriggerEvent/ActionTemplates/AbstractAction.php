<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\ActionTemplates;

use App\Models\Action;
use App\Models\Trigger;
use App\Packages\TriggerEvent\Traits\AssignArrayTrait;

abstract class AbstractAction
{
    use AssignArrayTrait;

    protected Action $action;
    protected Trigger $trigger;

    public function setAction(Action $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function setTrigger(Trigger $trigger): self
    {
        $this->trigger = $trigger;

        return $this;
    }

    protected function getRealFields(): array
    {
        return $this->action->variables;
    }

    protected function getFieldByName(string $fieldName)
    {
        return $this->action->variables[$fieldName] ?? null;
    }

    public function getActionVariables(): array
    {
        return $this->action->variables;
    }

    abstract public function getFieldsWithDefaults(): array;
}
