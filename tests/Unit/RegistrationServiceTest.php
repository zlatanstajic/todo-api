<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\RegistrationService;
use DateTimeInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

class RegistrationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_register_creates_user_and_returns_token(): void
    {
        $user = new class extends User
        {
            public function createToken(string $name,
                array $abilities = ['*'], ?DateTimeInterface $expiresAt = null
            ) {
                return new class
                {
                    public string $plainTextToken = 'plain-token';
                };
            }
        };

        $data = [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'password' => 'secret-password',
        ];

        $repo = Mockery::mock(UserRepository::class);
        $repo->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($user);

        $service = new RegistrationService($repo);

        $result = $service->register($data);

        $this->assertSame($user, $result['user']);
        $this->assertSame('plain-token', $result['token']);
    }
}
