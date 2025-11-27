<?php

namespace NeoPhp\Auth\Access;

use Exception;

/**
 * Authorization Exception
 * 
 * Thrown when user is not authorized to perform an action
 */
class AuthorizationException extends Exception
{
    protected $code = 403;
    protected $message = 'This action is unauthorized.';

    public function __construct(string $message = null, int $code = 403)
    {
        parent::__construct($message ?? $this->message, $code);
    }
}
