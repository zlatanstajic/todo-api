<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TokenService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Token Controller
 */
class TokenController extends Controller
{
    public function __construct(public readonly TokenService $tokenService)
    {
        //
    }

    /**
     * Authenticate user.
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string', 'min:5'],
            ]);

            return $this->successResponse([
                'token' => $this->tokenService->authenticate(
                    $data['email'],
                    $data['password'],
                ),
            ]);
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }

    /**
     * Revoke the current access token.
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $this->tokenService->revoke($request->user());

            return $this->successResponse();
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }

    /**
     * Revoke all of the user's access tokens.
     */
    public function destroyAll(Request $request): JsonResponse
    {
        try {
            $this->tokenService->revokeAll($request->user());

            return $this->successResponse();
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }
}
