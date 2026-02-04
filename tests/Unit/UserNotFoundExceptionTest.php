<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\User\UserNotFoundException;
use Exception;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class UserNotFoundExceptionTest extends TestCase
{
    public function test_construct_sets_message_and_code(): void
    {
        $c = new Container;
        Container::setInstance($c);

        $c->singleton('translator', fn () => new class
        {
            public function get($key, $replace = [], $locale = null)
            {
                return 'user not found';
            }
        });

        $ex = new UserNotFoundException;

        $this->assertSame('user not found', $ex->getMessage());
        $this->assertSame(Response::HTTP_NOT_FOUND, $ex->getCode());
        $this->assertInstanceOf(Exception::class, $ex);
        $this->assertInstanceOf(UserNotFoundException::class, $ex);
    }
}
