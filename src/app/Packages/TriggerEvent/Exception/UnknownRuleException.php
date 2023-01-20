<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Exception;

use Exception;

class UnknownRuleException extends Exception
{
    protected $message = 'Правило не найдено';
}
