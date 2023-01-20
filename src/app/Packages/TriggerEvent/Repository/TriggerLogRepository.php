<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Repository;

use App\Models\TriggerLog;
use App\Packages\TriggerEvent\Dto\CreateTriggerLogDTO;

class TriggerLogRepository
{
    public const TYPE_PUSH = 1;
    public const TYPE_PRIVATE_MESSAGE = 2;

    public function create(CreateTriggerLogDTO $createTriggerLogsDTO): ?TriggerLog
    {
        $triggerLog = new TriggerLog();

        $triggerLog->promoCode = $createTriggerLogsDTO->getPromoCode();
        $triggerLog->trigger_id = $createTriggerLogsDTO->getTriggerId();
        $triggerLog->external_user_id = $createTriggerLogsDTO->getExternalUserId();
        $triggerLog->type = $createTriggerLogsDTO->getType();
        $triggerLog->message = $createTriggerLogsDTO->getMessage();
        $triggerLog->action_id = $createTriggerLogsDTO->getActionId();
        $triggerLog->action_template_name = $createTriggerLogsDTO->getActionTemplate();
        $triggerLog->sent = $createTriggerLogsDTO->getSent();

        return $triggerLog->save() ? $triggerLog : null;
    }
}
