<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PasswordResetService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Password Reset Controller
 */
class PasswordResetController extends Controller
{
    public function __construct(public readonly PasswordResetService $passwordResetService)
    {
        //
    }

    /**
     * Send a password reset link.
     */
    public function forgot(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'email'],
            ]);

            $this->passwordResetService->sendResetLink($data['email']);

            // Always return the same generic message (enumeration-safe).
            return $this->successResponse(['message' => __('messages.password.forgot')]);
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }

    /**
     * Reset the user's password.
     */
    public function reset(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'token' => ['required', 'string'],
                'email' => ['required', 'email'],
                'password' => ['required', 'confirmed', PasswordRule::defaults()],
            ]);

            $status = $this->passwordResetService->reset($data);

            if ($status === Password::PASSWORD_RESET) {
                return $this->successResponse(['message' => __('messages.password.reset_success')]);
            }

            return $this->errorResponse(
                __('messages.password.reset_failed'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }
}
