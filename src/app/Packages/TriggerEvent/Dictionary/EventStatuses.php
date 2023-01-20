<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Dictionary;

class EventStatuses
{
    public const LIST = [
        self::NEW => 'Новый',
        self::CHECKED => 'Помечен',
        self::IN_PROCESS => 'В процессе',
        self::PROCESSED => 'Выполнен',
        self::ERROR => 'Ошибка',
        self::SKIPPED => 'Не было активных триггеров',
        self::DELAYED => 'Отложен',
    ];

    public const NEW = 0;
    public const CHECKED = 1;
    public const IN_PROCESS = 2;
    public const PROCESSED = 3;
    public const ERROR = 4;
    public const SKIPPED = 5;
    public const DELAYED = 6;
}
