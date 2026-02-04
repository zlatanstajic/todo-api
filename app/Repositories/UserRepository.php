<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

/**
 * User Repository
 */
class UserRepository
{
    /**
     * The model class name.
     */
    protected string $model = User::class;

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model::where('email', $email)->first();
    }
}
