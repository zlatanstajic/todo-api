<?php

declare(strict_types=1);

namespace App\Exceptions\Timeline;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when a timeline is not found.
 */
class TimelineNotFoundException extends ApiException
{
    /**
     * Construct the exception.
     */
    public function __construct()
    {
        parent::__construct(
            __('messages.timeline.not_found'),
            Response::HTTP_NOT_FOUND
        );
    }
}
