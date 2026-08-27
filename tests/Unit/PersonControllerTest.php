<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\PersonController;
use App\Services\PersonService;
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeResponseFactory;

class PersonControllerTest extends TestCase
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
        $c->singleton('config', fn (): Repository => new Repository([
            'timelines' => [
                'default_locale' => 'en',
                'locales' => ['en', 'sr'],
            ],
        ]));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_index_returns_success_with_collection(): void
    {
        $service = Mockery::mock(PersonService::class);
        $service->shouldReceive('getAllPeople')
            ->once()
            ->with('en')
            ->andReturn(new Collection([$this->makePerson()]));

        $controller = new PersonController($service);

        $resp = $controller->index($this->requestWithLocale());

        $this->assertInstanceOf(JsonResponse::class, $resp);
        $this->assertSame(200, $resp->getStatusCode());
        $this->assertNotEmpty($resp->getData(true));
    }

    public function test_index_passes_requested_locale(): void
    {
        $service = Mockery::mock(PersonService::class);
        $service->shouldReceive('getAllPeople')
            ->once()
            ->with('sr')
            ->andReturn(new Collection([]));

        $controller = new PersonController($service);

        $resp = $controller->index($this->requestWithLocale('sr'));

        $this->assertSame(200, $resp->getStatusCode());
    }

    public function test_index_rejects_invalid_locale(): void
    {
        $service = Mockery::mock(PersonService::class);
        $service->shouldNotReceive('getAllPeople');

        $controller = new PersonController($service);

        $resp = $controller->index($this->requestWithLocale('de'));

        $this->assertSame(422, $resp->getStatusCode());
    }

    public function test_index_hides_unexpected_exception_details(): void
    {
        Log::shouldReceive('error')->once();

        $service = Mockery::mock(PersonService::class);
        $service->shouldReceive('getAllPeople')
            ->once()
            ->andThrow(new Exception('secret sql details'));

        $controller = new PersonController($service);

        $resp = $controller->index($this->requestWithLocale());

        $this->assertSame(500, $resp->getStatusCode());
        // Generic translated message, not the raw exception message.
        $this->assertSame(['error' => 'ok'], $resp->getData(true));
    }

    public function test_show_returns_success_when_found(): void
    {
        $service = Mockery::mock(PersonService::class);
        $service->shouldReceive('getPersonBySlug')
            ->once()
            ->with('en', 'nikola-tesla')
            ->andReturn($this->makePerson());

        $controller = new PersonController($service);

        $resp = $controller->show($this->requestWithLocale(), 'nikola-tesla');

        $this->assertSame(200, $resp->getStatusCode());
        $this->assertArrayHasKey('data', $resp->getData(true));
    }

    public function test_show_returns_not_found_when_missing(): void
    {
        $service = Mockery::mock(PersonService::class);
        $service->shouldReceive('getPersonBySlug')
            ->once()
            ->with('en', 'unknown')
            ->andReturnNull();

        $controller = new PersonController($service);

        $resp = $controller->show($this->requestWithLocale(), 'unknown');

        $this->assertSame(404, $resp->getStatusCode());
    }

    private function requestWithLocale(?string $locale = null)
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('query')
            ->with('locale', 'en')
            ->andReturn($locale ?? 'en');

        return $request;
    }

    private function makePerson(): array
    {
        return [
            'slug' => 'nikola-tesla',
            'locale' => 'en',
            'name' => 'Nikola Tesla',
            'timelines' => [
                ['slug' => 'timeline-a', 'title' => 'Timeline A', 'description' => 'Inventor.'],
            ],
        ];
    }
}
