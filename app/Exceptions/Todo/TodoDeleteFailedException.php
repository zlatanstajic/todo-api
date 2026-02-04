<?php

declare(strict_types=1);

namespace App\Exceptions\Todo;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when a todo deletion fails.
 */
class TodoDeleteFailedException extends Exception
{
    /**
     * Construct the exception.
     */
    public function __construct()
    {
        parent::__construct(
            __('messages.todo.delete_failed'),
            Response::HTTP_CONFLICT
        );
    }
}
