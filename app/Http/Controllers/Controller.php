<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Exceptions\InvalidLocaleException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base Controller
 */
abstract class Controller
{
    /**
     * Send a success response.
     */
    protected function successResponse(
        mixed $data = [],
        string $message = '',
        int $code = Response::HTTP_OK
    ): JsonResponse {
        if (empty($message)) {
            $message = __('messages.default.success');
        }

        return response()->json([
            'data' => $data,
        ], $code);
    }

    /**
     * Send an error response.
     *
     * @param  array<string, array<int, string>>  $errors
     */
    protected function errorResponse(
        string $message,
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        array $errors = []
    ): JsonResponse {
        $payload = ['error' => $message];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $code);
    }

    /**
     * Render an exception as an error response.
     *
     * Validation and domain exception messages are safe to expose;
     * everything else is logged and mapped to a generic message.
     */
    protected function exceptionResponse(Exception $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $e->errors()
            );
        }

        if ($e instanceof ApiException) {
            return $this->errorResponse(
                $e->getMessage(),
                $this->sanitizeCode($e->getCode())
            );
        }

        Log::error($e->getMessage(), ['exception' => $e]);

        return $this->errorResponse(__('messages.error.server_error'));
    }

    /**
     * Resolve the requested locale, falling back to the default.
     *
     * @throws InvalidLocaleException
     */
    protected function locale(Request $request): string
    {
        $locale = (string) $request->query(
            'locale',
            (string) config('timelines.default_locale')
        );

        throw_unless(
            in_array($locale, (array) config('timelines.locales'), true),
            InvalidLocaleException::class
        );

        return $locale;
    }

    /**
     * Restrict exception codes to valid HTTP error statuses.
     */
    private function sanitizeCode(mixed $code): int
    {
        if (is_int($code) && $code >= 400 && $code < 600) {
            return $code;
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
