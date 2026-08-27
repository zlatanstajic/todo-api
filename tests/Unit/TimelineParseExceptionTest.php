<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\Timeline\TimelineParseException;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class TimelineParseExceptionTest extends TestCase
{
    public function test_construct_sets_message_and_code(): void
    {
        $ex = new TimelineParseException('file.txt: broken');

        $this->assertSame('file.txt: broken', $ex->getMessage());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $ex->getCode());
        $this->assertInstanceOf(Exception::class, $ex);
    }
}
