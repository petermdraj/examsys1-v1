<?php

namespace App\Exceptions;

use RuntimeException;

class PlanLimitExceededException extends RuntimeException
{
    public function __construct(string $message = 'Your current plan does not allow this action.')
    {
        parent::__construct($message);
    }
}
