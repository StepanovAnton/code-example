<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Contract;

class OrderStatus
{
    public const LIST = [
        self::FINISHED => 'Завершен (FINISHED)',
    ];

    public const UNDEFINED = 0;
    public const CREATED = 1;
    public const PLACED = 2;
    public const ACCEPTED = 3;
    public const ASSEMBLE_COMPLETE = 4;
    public const ASSEMBLE_INCOMPLETE = 5;
    public const COMPLETION_SEND = 6;
    public const DELIVERING = 7;
    public const SHIPMENT = 8;
    public const TRIP_ASSIGNED = 9;
    public const TRIP_ASSIGNMENT = 10;
    public const WAITING_CLIENT = 11;
    public const DELIVERED = 12;
    public const FAILURE = 13;
    public const CANCELED = 14;
    public const WAITING = 15;
    public const BUILD = 16;
    public const FINISHED = 17;
}
