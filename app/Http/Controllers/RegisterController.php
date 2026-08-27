<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Services\RegistrationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

/**
 * Register Controller
 */
class RegisterController extends Controller
{
    public function __construct(public readonly RegistrationService $registrationService)
    {
        //
    }

    /**
     * Register a new user.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            $result = $this->registrationService->register($data);

            return $this->successResponse(
                data: [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ],
                code: Response::HTTP_CREATED
            );
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }
}
