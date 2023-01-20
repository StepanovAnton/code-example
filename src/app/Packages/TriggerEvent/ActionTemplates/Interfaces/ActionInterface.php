<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\ActionTemplates\Interfaces;

use App\Models\Event;
use App\Models\Trigger;

interface ActionInterface
{
    public function processAction(Event $event, Trigger $trigger): bool;

    public static function getName(): string;

    public static function getLabel(): string;
}
