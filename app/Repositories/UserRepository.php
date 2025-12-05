<?php

namespace App\Repositories;

use App\Models\User;

/**
 * User Repository
 *
 * @package App\Repositories
 */
class UserRepository
{
    /**
     * Find user by email.
     *
     * @param string $email
     *
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
