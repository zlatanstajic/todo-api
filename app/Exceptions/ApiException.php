<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Base class for domain exceptions whose message and HTTP status
 * code are safe to expose to API clients.
 */
abstract class ApiException extends Exception
{
    //
}
