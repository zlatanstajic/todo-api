<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\User\UserInvalidCredentialsException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\TokenService;
use DateTimeInterface;
use Illuminate\Support\Facades\Hash;
use Mockery;
use PHPUnit\Framework\TestCase;

class TokenServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_authenticate_returns_token_string(): void
    {
        $user = new class extends User
        {
            public string $password = 'hashed';

            public function createToken(string $name,
                array $abilities = [], ?DateTimeInterface $expiresAt = null
            ) {
                return new class
                {
                    public string $plainTextToken = 'plain-token';
                };
            }
        };

        $repo = Mockery::mock(UserRepository::class);
        $repo->shouldReceive('findByEmail')
            ->once()
            ->with('a@b.c')
            ->andReturn($user);

        Hash::shouldReceive('check')
            ->once()
            ->with('pw', 'hashed')
            ->andReturnTrue();

        $service = new TokenService($repo);

        $this->assertSame('plain-token',
            $service->authenticate('a@b.c', 'pw')
        );
    }

    public function test_authenticate_throws_not_found(): void
    {
        $repo = Mockery::mock(UserRepository::class);
        $repo->shouldReceive('findByEmail')
            ->once()
            ->with('x@x.x')
            ->andReturnNull();

        // Provide translator binding used by exception construction.
        $c = new \Illuminate\Container\Container;
        \Illuminate\Container\Container::setInstance($c);
        $c->singleton('translator', fn () => new class
        {
            public function get($key, $replace = [], $locale = null)
            {
                return 'not found';
            }
        });

        $service = new TokenService($repo);

        $this->expectException(UserNotFoundException::class);

        $service->authenticate('x@x.x', 'pw');
    }

    public function test_authenticate_throws_invalid_credentials(): void
    {
        $user = new class extends User
        {
            public string $password = 'hashed';
        };

        $repo = Mockery::mock(UserRepository::class);
        $repo->shouldReceive('findByEmail')
            ->once()
            ->with('a@b.c')
            ->andReturn($user);

        Hash::shouldReceive('check')
            ->once()
            ->with('wrong', 'hashed')
            ->andReturnFalse();

        $service = new TokenService($repo);

        $this->expectException(UserInvalidCredentialsException::class);

        $service->authenticate('a@b.c', 'wrong');
    }
}
