<?php

declare(strict_types=1);

namespace App\Exceptions\Timeline;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when a timeline deletion fails.
 */
class TimelineDeleteFailedException extends ApiException
{
    /**
     * Construct the exception.
     */
    public function __construct()
    {
        parent::__construct(
            __('messages.timeline.delete_failed'),
            Response::HTTP_CONFLICT
        );
    }
}
