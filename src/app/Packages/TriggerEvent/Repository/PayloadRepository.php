<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Repository;

use App\Models\Payload;

class PayloadRepository
{
    public function create(int $eventId, string $objectType, string $objectValue): ?Payload
    {
        $payload = new Payload();

        $payload->event_id = $eventId;
        $payload->object_type = $objectType;
        $payload->object_value = $objectValue;

        return $payload->save() ? $payload : null;
    }

    public function getEventIdByObjectTypeAndObjectValue(string $objectType, string $objectValue)
    {
    }
}
