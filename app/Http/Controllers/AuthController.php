<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Authentication Controller
 *
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    /**
     * @param AuthService $authService
     */
    public function __construct(readonly AuthService $authService)
    {
        //
    }

    /**
     * Authenticate user.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function authenticate(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:5',
            ]);

            return $this->sendSuccessResponse([
                'token' => $this->authService->authenticate(
                    $data['email'],
                    $data['password'],
                ),
            ]);
        } catch (Exception $e) {
            return $this->sendErrorResponse($e->getMessage(), $e->getCode());
        }
    }
}
