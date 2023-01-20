<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Dto;

class RuleTemplateDto
{
    private string $name;
    private string $label;
    private string $handler;
    private array $variables;

    public function __construct(
        string $name,
        string $label,
        string $handler,
        array $variables
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->handler = $handler;
        $this->variables = $variables;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getHandler(): string
    {
        return $this->handler;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }
}
