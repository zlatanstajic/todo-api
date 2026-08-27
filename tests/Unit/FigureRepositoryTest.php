<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Figure as BaseFigure;
use App\Repositories\FigureRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class FigureStub extends BaseFigure
{
    public static $queryBuilder = null;

    public static function query()
    {
        return static::$queryBuilder;
    }
}

class FigureRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        FigureStub::$queryBuilder = null;

        Mockery::close();
    }

    public function test_get_all_by_locale_scopes_to_timeline_locale(): void
    {
        $coll = new Collection([]);

        $inner = Mockery::mock(Builder::class);
        $inner->shouldReceive('where')->once()->with('locale', 'en')->andReturnSelf();

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('whereHas')
            ->once()
            ->andReturnUsing(function ($relation, $callback) use ($builder, $inner) {
                $callback($inner);

                return $builder;
            });
        $builder->shouldReceive('with')->once()->with('timeline')->andReturnSelf();
        $builder->shouldReceive('orderBy')->once()->with('slug')->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn($coll);

        FigureStub::$queryBuilder = $builder;

        $this->assertSame($coll, $this->repo()->getAllByLocale('en'));
    }

    public function test_get_by_slug_filters_by_slug(): void
    {
        $coll = new Collection([]);

        $inner = Mockery::mock(Builder::class);
        $inner->shouldReceive('where')->once()->with('locale', 'sr')->andReturnSelf();

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('whereHas')
            ->once()
            ->andReturnUsing(function ($relation, $callback) use ($builder, $inner) {
                $callback($inner);

                return $builder;
            });
        $builder->shouldReceive('with')->once()->with('timeline')->andReturnSelf();
        $builder->shouldReceive('where')->once()->with('slug', 'a-person')->andReturnSelf();
        $builder->shouldReceive('orderBy')->once()->with('id')->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn($coll);

        FigureStub::$queryBuilder = $builder;

        $this->assertSame($coll, $this->repo()->getBySlug('sr', 'a-person'));
    }

    private function repo(): FigureRepository
    {
        return new class extends FigureRepository
        {
            public function __construct()
            {
                $this->model = FigureStub::class;
            }
        };
    }
}
