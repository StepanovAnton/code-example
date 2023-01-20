<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Exception;

use Exception;

class UnknownActionException extends Exception
{
    protected $message = 'Unknown action';
}
