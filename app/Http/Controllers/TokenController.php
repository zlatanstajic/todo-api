<?php

namespace App\Http\Controllers;

use App\Services\TokenService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Token Controller
 *
 * @package App\Http\Controllers
 */
class TokenController extends Controller
{
    /**
     * @param TokenService $tokenService
     */
    public function __construct(readonly TokenService $tokenService)
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
    public function create(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:5',
            ]);

            return $this->successResponse([
                'token' => $this->tokenService->authenticate(
                    $data['email'],
                    $data['password'],
                ),
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
