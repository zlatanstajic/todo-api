<?php

namespace App\Services;

use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

/**
 * TokenService handles user authentication logic.
 *
 * @package App\Services
 */
class TokenService
{
    /**
     * @param UserRepository $userRepository
     */
    public function __construct(readonly UserRepository $userRepository)
    {
        //
    }

    /**
     * Authenticate user.
     *
     * @param string $email
     * @param string $password
     *
     * @return string
     *
     * @throws UserNotFoundException
     * @throws InvalidCredentialsException
     */
    public function authenticate(string $email, string $password): string
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new UserNotFoundException();
        } elseif (!$this->verifyPassword($user, $password)) {
            throw new InvalidCredentialsException();
        }

        return $user->createToken('API Token')->plainTextToken;
    }

    /**
     * Verify user password.
     *
     * @param User $user
     * @param string $password
     *
     * @return bool
     */
    private function verifyPassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }
}
