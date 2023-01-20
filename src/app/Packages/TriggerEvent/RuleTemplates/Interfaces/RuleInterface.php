<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\RuleTemplates\Interfaces;

use App\Models\Event;
use App\Models\Trigger;

interface RuleInterface
{
    public function processRule(Event $event, Trigger $trigger): bool;

    public static function getName(): string;

    public static function getLabel(): string;
}
