<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\RegisterController;
use App\Models\User;
use App\Services\RegistrationService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeResponseFactory;

class RegisterControllerTest extends TestCase
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

    public function test_store_creates_user_and_returns_201(): void
    {
        $data = [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'password' => 'secret-password',
        ];

        $user = new User;
        $user->id = 1;
        $user->name = 'Jane';
        $user->email = 'jane@example.com';

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->once()->andReturn($data);

        $service = Mockery::mock(RegistrationService::class);
        $service->shouldReceive('register')
            ->once()
            ->with($data)
            ->andReturn(['user' => $user, 'token' => 'tok123']);

        $controller = new RegisterController($service);

        $resp = $controller->store($request);

        $this->assertInstanceOf(JsonResponse::class, $resp);
        $this->assertSame(201, $resp->getStatusCode());

        $body = $resp->getData(true);

        $this->assertSame('tok123', $body['data']['token']);
        $this->assertSame(User::class, $body['data']['user']['type']);
        $this->assertSame(1, $body['data']['user']['id']);
        $this->assertArrayNotHasKey('password', $body['data']['user']['attributes']);
    }

    public function test_store_returns_422_on_validation_failure(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')
            ->once()
            ->andThrow($this->makeValidationException());

        $service = Mockery::mock(RegistrationService::class);
        $service->shouldNotReceive('register');

        $controller = new RegisterController($service);

        $resp = $controller->store($request);

        $this->assertSame(422, $resp->getStatusCode());

        $body = $resp->getData(true);

        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('email', $body['errors']);
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
