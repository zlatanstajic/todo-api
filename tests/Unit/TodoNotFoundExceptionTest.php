<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\Todo\TodoNotFoundException;
use Exception;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class TodoNotFoundExceptionTest extends TestCase
{
    public function test_construct_sets_message_and_code(): void
    {
        $c = new Container;
        Container::setInstance($c);

        $c->singleton('translator', fn () => new class
        {
            public function get($key, $replace = [], $locale = null)
            {
                return 'not found';
            }
        });

        $ex = new TodoNotFoundException;

        $this->assertSame('not found', $ex->getMessage());
        $this->assertSame(Response::HTTP_NOT_FOUND, $ex->getCode());
        $this->assertInstanceOf(Exception::class, $ex);
        $this->assertInstanceOf(
            TodoNotFoundException::class,
            $ex
        );
    }
}
