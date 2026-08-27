<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when an unsupported locale is requested.
 */
class InvalidLocaleException extends ApiException
{
    /**
     * Construct the exception.
     */
    public function __construct()
    {
        parent::__construct(
            __('messages.error.invalid_locale'),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
