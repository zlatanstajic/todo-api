<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * PasswordResetService handles lost-password recovery logic.
 */
class PasswordResetService
{
    /**
     * Send a password reset link to the given email.
     */
    public function sendResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    /**
     * Reset the user's password using the broker.
     *
     * @param  array<string, mixed>  $credentials
     */
    public function reset(array $credentials): string
    {
        return Password::reset(
            $credentials,
            function (User $user, string $password): void {
                $user->forceFill(['password' => $password]);
                $user->setRememberToken(Str::random(60));
                $user->save();

                $user->tokens()->delete();
            }
        );
    }
}
