<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Dto;

class CreateActionDTO
{
    private string $title;
    private array $variables;
    private string $template;

    public function __construct(string $title, array $variables, string $template)
    {
        $this->title = $title;
        $this->variables = $variables;
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }
}
