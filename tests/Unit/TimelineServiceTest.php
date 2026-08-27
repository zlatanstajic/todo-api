<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Timeline;
use App\Repositories\TimelineRepository;
use App\Services\TimelineService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class TimelineServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_get_all_timelines_returns_collection(): void
    {
        $coll = new Collection([]);

        $repo = Mockery::mock(TimelineRepository::class);
        $repo->shouldReceive('getAll')->once()->with('en')
            ->andReturn($coll);

        $service = new TimelineService($repo);

        $this->assertSame($coll, $service->getAllTimelines('en'));
    }

    public function test_get_timeline_by_slug_returns_timeline(): void
    {
        $timeline = new Timeline;

        $repo = Mockery::mock(TimelineRepository::class);
        $repo->shouldReceive('findBySlug')->once()->with('en', 'some-slug')
            ->andReturn($timeline);

        $service = new TimelineService($repo);

        $this->assertSame($timeline, $service->getTimelineBySlug('en', 'some-slug'));
    }

    public function test_create_timeline_calls_repository(): void
    {
        $data = ['title' => 'x'];
        $timeline = new Timeline;

        $repo = Mockery::mock(TimelineRepository::class);
        $repo->shouldReceive('create')->once()->with($data)
            ->andReturn($timeline);

        $service = new TimelineService($repo);

        $this->assertSame($timeline, $service->createTimeline($data));
    }

    public function test_update_timeline_calls_repository(): void
    {
        $data = ['title' => 'y'];
        $timeline = new Timeline;

        $repo = Mockery::mock(TimelineRepository::class);
        $repo->shouldReceive('update')->once()->with('en', 'some-slug', $data)
            ->andReturn($timeline);

        $service = new TimelineService($repo);

        $this->assertSame($timeline, $service->updateTimeline('en', 'some-slug', $data));
    }

    public function test_delete_timeline_calls_repository(): void
    {
        $repo = Mockery::mock(TimelineRepository::class);
        $repo->shouldReceive('delete')->once()->with('en', 'some-slug')
            ->andReturnTrue();

        $service = new TimelineService($repo);

        $this->assertTrue($service->deleteTimeline('en', 'some-slug'));
    }
}
