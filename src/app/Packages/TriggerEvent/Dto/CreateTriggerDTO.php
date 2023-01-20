<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Dto;

class CreateTriggerDTO
{
    private array $actionIds;
    private string $title;
    private string $eventName;
    private array $ruleIds;
    private bool $enabled;

    public function __construct(string $title, string $eventName, array $actionIds, array $ruleIds, bool $enabled = true)
    {
        $this->title = $title;
        $this->eventName = $eventName;
        $this->actionIds = $actionIds;
        $this->ruleIds = $ruleIds;
        $this->enabled = $enabled;
    }

    /**
     * @return array
     */
    public function getActionIds(): array
    {
        return $this->actionIds;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * @return array
     */
    public function getRuleIds(): array
    {
        return $this->ruleIds;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
