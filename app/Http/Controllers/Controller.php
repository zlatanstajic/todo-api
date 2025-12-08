<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base Controller
 *
 * @package App\Http\Controllers
 */
abstract class Controller
{
    /**
     * Send a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int    $code
     *
     * @return JsonResponse
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
     * @param string $message
     * @param int    $code
     *
     * @return JsonResponse
     */
    protected function errorResponse(
        string $message,
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse {
        return response()->json([
            'error' => $message,
        ], $code);
    }
}
