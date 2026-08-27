<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Services\PasswordResetService;
use Illuminate\Support\Facades\Password;
use Mockery;
use PHPUnit\Framework\TestCase;

class PasswordResetServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_send_reset_link_returns_broker_status(): void
    {
        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => 'a@b.c'])
            ->andReturn(Password::RESET_LINK_SENT);

        $service = new PasswordResetService;

        $this->assertSame(
            Password::RESET_LINK_SENT,
            $service->sendResetLink('a@b.c')
        );
    }

    public function test_reset_runs_callback_and_returns_status(): void
    {
        $relation = Mockery::mock();
        $relation->shouldReceive('delete')->once();

        $user = new class($relation) extends User
        {
            public bool $saved = false;

            public function __construct(private $relation)
            {
                //
            }

            public function casts(): array
            {
                return [];
            }

            public function save(array $options = [])
            {
                $this->saved = true;

                return true;
            }

            public function tokens()
            {
                return $this->relation;
            }
        };

        $credentials = [
            'token' => 't',
            'email' => 'a@b.c',
            'password' => 'new-password',
        ];

        Password::shouldReceive('reset')
            ->once()
            ->with($credentials, Mockery::type('callable'))
            ->andReturnUsing(function (array $creds, callable $callback) use ($user): string {
                $callback($user, 'new-password');

                return Password::PASSWORD_RESET;
            });

        $service = new PasswordResetService;

        $status = $service->reset($credentials);

        $this->assertSame(Password::PASSWORD_RESET, $status);
        $this->assertTrue($user->saved);
        $this->assertSame('new-password', $user->password);
        $this->assertNotNull($user->getRememberToken());
    }

    public function test_reset_returns_failure_status(): void
    {
        Password::shouldReceive('reset')
            ->once()
            ->andReturn(Password::INVALID_TOKEN);

        $service = new PasswordResetService;

        $this->assertSame(
            Password::INVALID_TOKEN,
            $service->reset(['token' => 'bad'])
        );
    }
}
