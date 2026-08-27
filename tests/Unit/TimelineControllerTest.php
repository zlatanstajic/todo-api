<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\ApiException;
use App\Http\Controllers\TimelineController;
use App\Models\Timeline;
use App\Services\TimelineService;
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeResponseFactory;

class TimelineControllerTest extends TestCase
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
        $service = Mockery::mock(TimelineService::class);
        $service->shouldReceive('getAllTimelines')
            ->once()
            ->with('en')
            ->andReturn(new Collection([$this->makeTimeline(1)]));

        $controller = new TimelineController($service);

        $resp = $controller->index($this->requestWithLocale());

        $this->assertInstanceOf(JsonResponse::class, $resp);
        $this->assertSame(200, $resp->getStatusCode());

        $body = $resp->getData(true);

        $this->assertIsArray($body);
        $this->assertNotEmpty($body);
    }

    public function test_index_passes_requested_locale(): void
    {
        $service = Mockery::mock(TimelineService::class);
        $service->shouldReceive('getAllTimelines')
            ->once()
            ->with('sr')
            ->andReturn(new Collection([]));

        $controller = new TimelineController($service);

        $resp = $controller->index($this->requestWithLocale('sr'));

        $this->assertSame(200, $resp->getStatusCode());
    }

    public function test_index_rejects_invalid_locale(): void
    {
        $service = Mockery::mock(TimelineService::class);
        $service->shouldNotReceive('getAllTimelines');

        $controller = new TimelineController($service);

        $resp = $controller->index($this->requestWithLocale('de'));

        $this->assertSame(422, $resp->getStatusCode());
    }

    public function test_index_hides_unexpected_exception_details(): void
    {
        Log::shouldReceive('error')->once();

        $service = Mockery::mock(TimelineService::class);
        $service->shouldReceive('getAllTimelines')
            ->once()
            ->andThrow(new Exception('secret sql details'));

        $controller = new TimelineController($service);

        $resp = $controller->index($this->requestWithLocale());

        $this->assertSame(500, $resp->getStatusCode());
        // Generic translated message, not the raw exception message.
        $this->assertSame(['error' => 'ok'], $resp->getData(true));
    }

    public function test_domain_exception_with_invalid_code_maps_to_500(): void
    {
        $exception = new class extends ApiException
        {
            public function __construct()
            {
                parent::__construct('domain message', 0);
            }
        };

        $service = Mockery::mock(TimelineService::class);
        $service->shouldReceive('getAllTimelines')->once()->andThrow($exception);

        $controller = new TimelineController($service);

        $resp = $controller->index($this->requestWithLocale());

        $this->assertSame(500, $resp->getStatusCode());
        $this->assertSame(['error' => 'domain message'], $resp->getData(true));
    }

    public function test_show_returns_success_when_found(): void
    {
        $service = Mockery::mock(TimelineService::class);
        $service->shouldReceive('getTimelineBySlug')
            ->once()
            ->with('en', 'a-slug')
            ->andReturn($this->makeTimeline(4));

        $controller = new TimelineController($service);

        $resp = $controller->show($this->requestWithLocale(), 'a-slug');

        $this->assertSame(200, $resp->getStatusCode());
        $this->assertArrayHasKey('data', $resp->getData(true));
    }

    public function test_show_returns_not_found_when_missing(): void
    {
        $service = Mockery::mock(TimelineService::class);
        $service->shouldReceive('getTimelineBySlug')
            ->once()
            ->with('en', 'missing')
            ->andReturnNull();

        $controller = new TimelineController($service);

        $resp = $controller->show($this->requestWithLocale(), 'missing');

        $this->assertSame(404, $resp->getStatusCode());
    }

    public function test_store_validates_and_creates_timeline(): void
    {
        $data = ['locale' => 'en', 'slug' => 'x'];

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->once()->andReturn($data);

        $service = Mockery::mock(TimelineService::class);
        $service->shouldReceive('createTimeline')
            ->once()
            ->with(['locale' => 'en', 'slug' => 'x', 'translation_key' => 'x'])
            ->andReturn($this->makeTimeline(2));

        $controller = new TimelineController($service);

        $resp = $controller->store($request);

        $this->assertSame(201, $resp->getStatusCode(), print_r($resp->getData(true), true));
    }

    public function test_store_returns_422_on_validation_failure(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')
            ->once()
            ->andThrow($this->makeValidationException());

        $service = Mockery::mock(TimelineService::class);
        $service->shouldNotReceive('createTimeline');

        $controller = new TimelineController($service);

        $resp = $controller->store($request);

        $this->assertSame(422, $resp->getStatusCode());

        $body = $resp->getData(true);

        // Per-field error bag is exposed alongside the message.
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('title', $body['errors']);
    }

    public function test_store_hides_unexpected_exception_details(): void
    {
        Log::shouldReceive('error')->once();

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->once()->andReturn(['slug' => 'x']);

        $service = Mockery::mock(TimelineService::class);
        $service->shouldReceive('createTimeline')
            ->once()
            ->andThrow(new Exception('store fail'));

        $controller = new TimelineController($service);

        $resp = $controller->store($request);

        $this->assertSame(500, $resp->getStatusCode());
        $this->assertSame(['error' => 'ok'], $resp->getData(true));
    }

    public function test_update_calls_service_and_returns_resource(): void
    {
        $data = ['title' => 'y'];

        $request = $this->requestWithLocale();
        $request->shouldReceive('validate')->once()->andReturn($data);

        $service = Mockery::mock(TimelineService::class);
        $service->shouldReceive('updateTimeline')
            ->once()
            ->with('en', 'a-slug', $data)
            ->andReturn($this->makeTimeline(3));

        $controller = new TimelineController($service);

        $resp = $controller->update($request, 'a-slug');

        $this->assertSame(200, $resp->getStatusCode());
    }

    public function test_update_hides_unexpected_exception_details(): void
    {
        Log::shouldReceive('error')->once();

        $request = $this->requestWithLocale();
        $request->shouldReceive('validate')->once()->andReturn([]);

        $service = Mockery::mock(TimelineService::class);
        $service->shouldReceive('updateTimeline')
            ->once()
            ->andThrow(new Exception('update fail', 422));

        $controller = new TimelineController($service);

        $resp = $controller->update($request, 'a-slug');

        $this->assertSame(500, $resp->getStatusCode());
        $this->assertSame(['error' => 'ok'], $resp->getData(true));
    }

    public function test_destroy_returns_success_when_deleted(): void
    {
        $service = Mockery::mock(TimelineService::class);
        $service->shouldReceive('deleteTimeline')->once()->with('en', 'a-slug')
            ->andReturnTrue();

        $controller = new TimelineController($service);

        $resp = $controller->destroy($this->requestWithLocale(), 'a-slug');

        $this->assertSame(200, $resp->getStatusCode());
    }

    public function test_destroy_returns_conflict_when_delete_fails(): void
    {
        $service = Mockery::mock(TimelineService::class);
        $service->shouldReceive('deleteTimeline')->once()->with('en', 'a-slug')
            ->andReturnFalse();

        $controller = new TimelineController($service);

        $resp = $controller->destroy($this->requestWithLocale(), 'a-slug');

        $this->assertSame(409, $resp->getStatusCode());
    }

    private function requestWithLocale(?string $locale = null)
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('query')
            ->with('locale', 'en')
            ->andReturn($locale ?? 'en');

        return $request;
    }

    private function makeTimeline(int $id): Timeline
    {
        $timeline = new Timeline;

        $timeline->id = $id;
        $timeline->setRelation('figures', new Collection([]));
        $timeline->setRelation('translations', new Collection([]));

        return $timeline;
    }

    private function makeValidationException(): ValidationException
    {
        $validator = new Validator(
            new Translator(new ArrayLoader, 'en'),
            ['title' => null],
            ['title' => ['required']]
        );

        return new ValidationException($validator);
    }
}
