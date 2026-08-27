<?php

declare(strict_types=1);

namespace App\Exceptions\User;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when invalid credentials are provided.
 */
class UserInvalidCredentialsException extends ApiException
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
