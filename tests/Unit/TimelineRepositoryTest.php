<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\Timeline\TimelineNotFoundException;
use App\Models\Timeline as BaseTimeline;
use App\Repositories\TimelineRepository;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\TestCase;

class TimelineStub extends BaseTimeline
{
    public static $queryBuilder = null;

    public static $createReturn = null;

    public static function query()
    {
        return static::$queryBuilder;
    }

    public static function create(array $data = [])
    {
        return static::$createReturn ?? new BaseTimeline;
    }
}

class TimelineRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        TimelineStub::$queryBuilder = null;
        TimelineStub::$createReturn = null;

        Mockery::close();
    }

    public function test_get_all_returns_collection_without_locale(): void
    {
        $coll = new Collection([]);

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('with')->once()->with(['figures', 'translations'])->andReturnSelf();
        $builder->shouldNotReceive('where');
        $builder->shouldReceive('orderBy')->once()->with('slug')->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn($coll);

        TimelineStub::$queryBuilder = $builder;

        $this->assertSame($coll, $this->repo()->getAll());
    }

    public function test_get_all_filters_by_locale(): void
    {
        $coll = new Collection([]);

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('with')->once()->with(['figures', 'translations'])->andReturnSelf();
        $builder->shouldReceive('where')->once()->with('locale', 'sr')->andReturnSelf();
        $builder->shouldReceive('orderBy')->once()->with('slug')->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn($coll);

        TimelineStub::$queryBuilder = $builder;

        $this->assertSame($coll, $this->repo()->getAll('sr'));
    }

    public function test_find_by_slug_returns_timeline(): void
    {
        $timeline = new BaseTimeline;

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('with')->once()->with(['figures', 'translations'])->andReturnSelf();
        $builder->shouldReceive('where')->once()->with('locale', 'en')->andReturnSelf();
        $builder->shouldReceive('where')->once()->with('slug', 'a-slug')->andReturnSelf();
        $builder->shouldReceive('first')->once()->andReturn($timeline);

        TimelineStub::$queryBuilder = $builder;

        $this->assertSame($timeline, $this->repo()->findBySlug('en', 'a-slug'));
    }

    public function test_create_returns_timeline(): void
    {
        $timeline = new BaseTimeline;

        TimelineStub::$createReturn = $timeline;

        $this->assertSame($timeline, $this->repo()->create(['slug' => 'x']));
    }

    public function test_update_updates_and_returns_model(): void
    {
        $instance = new class extends BaseTimeline
        {
            public function update(array $attributes = [],
                array $options = [])
            {
                return true;
            }
        };

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('with')->once()->andReturnSelf();
        $builder->shouldReceive('where')->twice()->andReturnSelf();
        $builder->shouldReceive('first')->once()->andReturn($instance);

        TimelineStub::$queryBuilder = $builder;

        $this->assertSame($instance, $this->repo()->update('en', 'a-slug', ['title' => 'v']));
    }

    public function test_update_throws_when_not_found(): void
    {
        $this->expectException(TimelineNotFoundException::class);

        $c = new Container;
        Container::setInstance($c);
        $c->singleton('translator', fn () => new class
        {
            public function get($key, $replace = [], $locale = null)
            {
                return 'not found';
            }
        });

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('with')->once()->andReturnSelf();
        $builder->shouldReceive('where')->twice()->andReturnSelf();
        $builder->shouldReceive('first')->once()->andReturnNull();

        TimelineStub::$queryBuilder = $builder;

        $this->repo()->update('en', 'missing', ['title' => 'v']);
    }

    public function test_delete_returns_true_when_deleted(): void
    {
        $instance = new class extends BaseTimeline
        {
            public function delete()
            {
                return true;
            }
        };

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('with')->once()->andReturnSelf();
        $builder->shouldReceive('where')->twice()->andReturnSelf();
        $builder->shouldReceive('first')->once()->andReturn($instance);

        TimelineStub::$queryBuilder = $builder;

        $this->assertTrue($this->repo()->delete('en', 'a-slug'));
    }

    public function test_delete_returns_false_when_not_found(): void
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('with')->once()->andReturnSelf();
        $builder->shouldReceive('where')->twice()->andReturnSelf();
        $builder->shouldReceive('first')->once()->andReturnNull();

        TimelineStub::$queryBuilder = $builder;

        $this->assertFalse($this->repo()->delete('en', 'missing'));
    }

    public function test_upsert_with_figures_replaces_figures(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn ($callback) => $callback());

        $figuresRelation = Mockery::mock(HasMany::class);
        $figuresRelation->shouldReceive('delete')->once();
        $figuresRelation->shouldReceive('create')
            ->once()
            ->with([
                'name' => 'Some Person',
                'slug' => 'some-person',
                'description' => 'A description.',
            ]);

        $timeline = Mockery::mock(BaseTimeline::class);
        $timeline->shouldReceive('figures')->twice()->andReturn($figuresRelation);

        $attributes = [
            'locale' => 'en',
            'slug' => 'a-slug',
            'title' => 'A Title',
        ];

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('updateOrCreate')
            ->once()
            ->with(['locale' => 'en', 'slug' => 'a-slug'], $attributes)
            ->andReturn($timeline);

        TimelineStub::$queryBuilder = $builder;

        $result = $this->repo()->upsertWithFigures($attributes, [
            ['name' => 'Some Person', 'description' => 'A description.'],
        ]);

        $this->assertSame($timeline, $result);
    }

    public function test_prune_except_deletes_other_slugs_per_locale(): void
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('where')->once()->with('locale', 'en')->andReturnSelf();
        $builder->shouldReceive('where')->once()->with('locale', 'sr')->andReturnSelf();
        $builder->shouldReceive('whereNotIn')->once()->with('slug', ['kept-en'])->andReturnSelf();
        $builder->shouldReceive('whereNotIn')->once()->with('slug', ['kept-sr'])->andReturnSelf();
        $builder->shouldReceive('delete')->twice()->andReturn(2, 1);

        TimelineStub::$queryBuilder = $builder;

        $deleted = $this->repo()->pruneExcept([
            'en' => ['kept-en'],
            'sr' => ['kept-sr'],
        ]);

        $this->assertSame(3, $deleted);
    }

    private function repo(): TimelineRepository
    {
        return new class extends TimelineRepository
        {
            public function __construct()
            {
                $this->model = TimelineStub::class;
            }
        };
    }
}
