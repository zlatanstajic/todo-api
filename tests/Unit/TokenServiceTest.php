<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\User\UserInvalidCredentialsException;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\TokenService;
use DateTimeInterface;
use Illuminate\Container\Container;
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

    public function test_authenticate_runs_dummy_check_and_throws_when_user_missing(): void
    {
        $this->bindTranslator();

        $repo = Mockery::mock(UserRepository::class);
        $repo->shouldReceive('findByEmail')
            ->once()
            ->with('x@x.x')
            ->andReturnNull();

        // Dummy check runs once on the not-found path to equalize timing.
        Hash::shouldReceive('check')
            ->once()
            ->andReturnFalse();

        $service = new TokenService($repo);

        $this->expectException(UserInvalidCredentialsException::class);

        $service->authenticate('x@x.x', 'pw');
    }

    public function test_authenticate_throws_invalid_credentials_on_wrong_password(): void
    {
        $this->bindTranslator();

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

    public function test_revoke_deletes_current_access_token(): void
    {
        $token = Mockery::mock();
        $token->shouldReceive('delete')->once();

        $user = new class($token) extends User
        {
            public function __construct(private $token)
            {
                //
            }

            public function currentAccessToken()
            {
                return $this->token;
            }
        };

        $service = new TokenService(Mockery::mock(UserRepository::class));

        $service->revoke($user);

        $this->assertTrue(true);
    }

    public function test_revoke_all_deletes_all_tokens(): void
    {
        $relation = Mockery::mock();
        $relation->shouldReceive('delete')->once();

        $user = new class($relation) extends User
        {
            public function __construct(private $relation)
            {
                //
            }

            public function tokens()
            {
                return $this->relation;
            }
        };

        $service = new TokenService(Mockery::mock(UserRepository::class));

        $service->revokeAll($user);

        $this->assertTrue(true);
    }

    private function bindTranslator(): void
    {
        $c = new Container;
        Container::setInstance($c);
        $c->singleton('translator', fn () => new class
        {
            public function get($key, $replace = [], $locale = null)
            {
                return 'message';
            }
        });
    }
}
