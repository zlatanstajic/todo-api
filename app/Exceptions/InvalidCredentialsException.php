<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when invalid credentials are provided.
 *
 * @package App\Exceptions\Todo
 */
class InvalidCredentialsException extends Exception
{
    /**
     * Construct the exception.
     */
    public function __construct()
    {
        parent::__construct(
            __('messages.error.invalid_credentials'),
            Response::HTTP_UNAUTHORIZED
        );
    }
}
