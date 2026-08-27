<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\User\UserInvalidCredentialsException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

/**
 * TokenService handles user authentication logic.
 */
class TokenService
{
    /**
     * Bogus bcrypt hash used to equalize timing on the not-found path.
     */
    private const DUMMY_HASH = '$2y$12$ExarcWRwstdvGzAMc4Db6u6KxrhRH6ICUOLwnIE8roOLtYWTQ.Gg';

    public function __construct(public readonly UserRepository $userRepository)
    {
        //
    }

    /**
     * Authenticate user.
     *
     * @throws UserInvalidCredentialsException
     */
    public function authenticate(string $email, string $password): string
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user instanceof User) {
            // Run a dummy check to equalize timing against the found path.
            Hash::check($password, self::DUMMY_HASH);

            throw new UserInvalidCredentialsException;
        }

        throw_unless($this->verifyPassword($user, $password), UserInvalidCredentialsException::class);

        return $user->createToken('API Token')->plainTextToken;
    }

    /**
     * Revoke the user's current access token.
     */
    public function revoke(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Revoke all of the user's access tokens.
     */
    public function revokeAll(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Verify user password.
     */
    private function verifyPassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }
}
