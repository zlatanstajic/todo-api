<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\Todo\TodoDeleteFailedException;
use Exception;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class TodoDeleteFailedExceptionTest extends TestCase
{
    public function test_construct_sets_message_and_code(): void
    {
        $c = new Container;
        Container::setInstance($c);

        $c->singleton('translator', fn () => new class
        {
            public function get($key, $replace = [], $locale = null)
            {
                return 'delete failed';
            }
        });

        $ex = new TodoDeleteFailedException;

        $this->assertSame('delete failed', $ex->getMessage());
        $this->assertSame(Response::HTTP_CONFLICT, $ex->getCode());
        $this->assertInstanceOf(Exception::class, $ex);
        $this->assertInstanceOf(
            TodoDeleteFailedException::class,
            $ex
        );
    }
}
