<?php

declare(strict_types=1);

namespace App\Exceptions\Person;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when a person is not found.
 */
class PersonNotFoundException extends ApiException
{
    /**
     * Construct the exception.
     */
    public function __construct()
    {
        parent::__construct(
            __('messages.person.not_found'),
            Response::HTTP_NOT_FOUND
        );
    }
}
