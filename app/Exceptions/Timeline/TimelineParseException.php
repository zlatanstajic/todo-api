<?php

declare(strict_types=1);

namespace App\Exceptions\Timeline;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when a timeline txt file cannot be parsed.
 */
class TimelineParseException extends ApiException
{
    /**
     * Construct the exception.
     */
    public function __construct(string $message)
    {
        parent::__construct(
            $message,
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
