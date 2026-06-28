<?php

namespace App\Exceptions;

use Exception;

class InsufficientAiCreditsException extends Exception
{
    public function __construct(string $message = 'Insufficient AI credits. Please top up your wallet or upgrade your plan.')
    {
        parent::__construct($message);
    }
}
