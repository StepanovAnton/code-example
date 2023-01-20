<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\RuleTemplates;

use App\Models\Rule;
use App\Models\Trigger;
use App\Packages\TriggerEvent\Traits\AssignArrayTrait;

abstract class AbstractRule
{
    use AssignArrayTrait;

    protected Rule $rule;
    protected Trigger $trigger;

    /**
     * @param Rule $rule
     * @return AbstractRule
     */
    public function setRule(Rule $rule): self
    {
        $this->rule = $rule;

        return $this;
    }

    public function setTrigger(Trigger $trigger): self
    {
        $this->trigger = $trigger;

        return $this;
    }

    protected function getRealFields(): array
    {
        return $this->rule->variables;
    }

    protected function getFieldByName(string $fieldName)
    {
        return $this->rule->variables[$fieldName] ?? null;
    }

    public function getRuleVariables(): array
    {
        return $this->rule->variables;
    }

    abstract public function getFieldsWithDefaults(): array;
}
