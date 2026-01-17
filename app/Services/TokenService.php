<?php

namespace App\Services;

use App\Exceptions\User\UserInvalidCredentialsException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

/**
 * TokenService handles user authentication logic.
 */
class TokenService
{
    public function __construct(public readonly UserRepository $userRepository)
    {
        //
    }

    /**
     * Authenticate user.
     *
     * @throws UserNotFoundException
     * @throws UserInvalidCredentialsException
     */
    public function authenticate(string $email, string $password): string
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user) {
            throw new UserNotFoundException;
        } elseif (! $this->verifyPassword($user, $password)) {
            throw new UserInvalidCredentialsException;
        }

        return $user->createToken('API Token')->plainTextToken;
    }

    /**
     * Verify user password.
     */
    private function verifyPassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }
}
