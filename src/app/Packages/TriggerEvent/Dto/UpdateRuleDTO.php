<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Dto;

class UpdateRuleDTO
{
    private int $id;
    private string $title;
    private array $variables;
    private string $template;

    public function __construct(int $id, string $title, array $variables, string $template)
    {
        $this->id = $id;
        $this->title = $title;
        $this->variables = $variables;
        $this->template = $template;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
