<?php

namespace App\Exceptions\Todo;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when a todo is not found.
 *
 * @package App\Exceptions\Todo
 */
class TodoNotFoundException extends Exception
{
    /**
     * Construct the exception.
     */
    public function __construct()
    {
        parent::__construct(
            __('messages.todo.not_found'),
            Response::HTTP_NOT_FOUND
        );
    }
}
