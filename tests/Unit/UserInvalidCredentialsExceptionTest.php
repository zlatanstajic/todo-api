<?php

namespace Tests\Unit;

use App\Exceptions\User\UserInvalidCredentialsException;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class UserInvalidCredentialsExceptionTest extends TestCase
{
    public function test_construct_sets_message_and_code(): void
    {
        $c = new Container;
        Container::setInstance($c);

        $c->singleton('translator', function () {
            return new class
            {
                public function get($key, $replace = [], $locale = null)
                {
                    return 'invalid credentials';
                }
            };
        });

        $ex = new UserInvalidCredentialsException;

        $this->assertSame('invalid credentials', $ex->getMessage());
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $ex->getCode());
        $this->assertInstanceOf(\Exception::class, $ex);
    }
}
