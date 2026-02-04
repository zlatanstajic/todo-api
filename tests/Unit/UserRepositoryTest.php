<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User as BaseUser;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\TestCase;

class UserStub extends BaseUser
{
    public static $whereReturn = null;

    public static function where($column, $value)
    {
        return new class
        {
            public function first()
            {
                return UserStub::$whereReturn;
            }
        };
    }
}

class UserRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        UserStub::$whereReturn = null;
    }

    public function test_find_by_email_returns_user_when_found(): void
    {
        $user = new BaseUser;

        UserStub::$whereReturn = $user;

        $repo = new class extends UserRepository
        {
            public function __construct()
            {
                $this->model = UserStub::class;
            }
        };

        $this->assertSame($user, $repo->findByEmail('a@b.c'));
    }

    public function test_find_by_email_returns_null_when_not_found(): void
    {
        UserStub::$whereReturn = null;

        Log::shouldReceive('warning')->never();

        $repo = new class extends UserRepository
        {
            public function __construct()
            {
                $this->model = UserStub::class;
            }
        };

        $this->assertNull($repo->findByEmail('x@y.z'));
    }
}
