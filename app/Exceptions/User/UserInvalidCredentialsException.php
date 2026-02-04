<?php

declare(strict_types=1);

namespace App\Exceptions\User;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when invalid credentials are provided.
 */
class UserInvalidCredentialsException extends Exception
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
