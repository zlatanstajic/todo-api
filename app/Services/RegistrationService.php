<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

/**
 * RegistrationService handles user registration logic.
 */
class RegistrationService
{
    public function __construct(public readonly UserRepository $userRepository)
    {
        //
    }

    /**
     * Register a new user and issue an API token.
     *
     * @param  array<string, mixed>  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        $user = $this->userRepository->create($data);

        return [
            'user' => $user,
            'token' => $user->createToken('API Token')->plainTextToken,
        ];
    }
}
