<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\PasswordResetController;
use App\Services\PasswordResetService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeResponseFactory;

class PasswordResetControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $c = new Container;
        Container::setInstance($c);

        $c->singleton(fn (): ResponseFactory => new FakeResponseFactory);
        $c->singleton('translator', fn () => new class
        {
            public function get($k)
            {
                return 'ok';
            }
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_forgot_returns_generic_message_when_link_sent(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->once()->andReturn(['email' => 'a@b.c']);

        $service = Mockery::mock(PasswordResetService::class);
        $service->shouldReceive('sendResetLink')
            ->once()
            ->with('a@b.c')
            ->andReturn(Password::RESET_LINK_SENT);

        $controller = new PasswordResetController($service);

        $resp = $controller->forgot($request);

        $this->assertInstanceOf(JsonResponse::class, $resp);
        $this->assertSame(200, $resp->getStatusCode());
        $this->assertSame(['data' => ['message' => 'ok']], $resp->getData(true));
    }

    public function test_forgot_returns_same_message_when_user_invalid(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->once()->andReturn(['email' => 'a@b.c']);

        $service = Mockery::mock(PasswordResetService::class);
        $service->shouldReceive('sendResetLink')
            ->once()
            ->with('a@b.c')
            ->andReturn(Password::INVALID_USER);

        $controller = new PasswordResetController($service);

        $resp = $controller->forgot($request);

        $this->assertSame(200, $resp->getStatusCode());
        $this->assertSame(['data' => ['message' => 'ok']], $resp->getData(true));
    }

    public function test_forgot_returns_422_on_validation_failure(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')
            ->once()
            ->andThrow($this->makeValidationException());

        $service = Mockery::mock(PasswordResetService::class);
        $service->shouldNotReceive('sendResetLink');

        $controller = new PasswordResetController($service);

        $resp = $controller->forgot($request);

        $this->assertSame(422, $resp->getStatusCode());
    }

    public function test_reset_returns_success_on_password_reset(): void
    {
        $data = [
            'token' => 't',
            'email' => 'a@b.c',
            'password' => 'new-password',
        ];

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->once()->andReturn($data);

        $service = Mockery::mock(PasswordResetService::class);
        $service->shouldReceive('reset')
            ->once()
            ->with($data)
            ->andReturn(Password::PASSWORD_RESET);

        $controller = new PasswordResetController($service);

        $resp = $controller->reset($request);

        $this->assertSame(200, $resp->getStatusCode());
        $this->assertSame(['data' => ['message' => 'ok']], $resp->getData(true));
    }

    public function test_reset_returns_422_on_broker_failure(): void
    {
        $data = [
            'token' => 'bad',
            'email' => 'a@b.c',
            'password' => 'new-password',
        ];

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->once()->andReturn($data);

        $service = Mockery::mock(PasswordResetService::class);
        $service->shouldReceive('reset')
            ->once()
            ->with($data)
            ->andReturn(Password::INVALID_TOKEN);

        $controller = new PasswordResetController($service);

        $resp = $controller->reset($request);

        $this->assertSame(422, $resp->getStatusCode());
        $this->assertSame(['error' => 'ok'], $resp->getData(true));
    }

    public function test_reset_returns_422_on_validation_failure(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')
            ->once()
            ->andThrow($this->makeValidationException());

        $service = Mockery::mock(PasswordResetService::class);
        $service->shouldNotReceive('reset');

        $controller = new PasswordResetController($service);

        $resp = $controller->reset($request);

        $this->assertSame(422, $resp->getStatusCode());
    }

    private function makeValidationException(): ValidationException
    {
        $validator = new Validator(
            new Translator(new ArrayLoader, 'en'),
            ['email' => null],
            ['email' => ['required']]
        );

        return new ValidationException($validator);
    }
}
