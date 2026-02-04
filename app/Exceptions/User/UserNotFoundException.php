<?php

declare(strict_types=1);

namespace App\Exceptions\User;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when a user is not found.
 */
class UserNotFoundException extends Exception
{
    /**
     * Construct the exception.
     */
    public function __construct()
    {
        parent::__construct(
            __('messages.user.not_found'),
            Response::HTTP_NOT_FOUND
        );
    }
}
