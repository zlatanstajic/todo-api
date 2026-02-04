<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mockery;
use PHPUnit\Framework\TestCase;

class UserScopeTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_apply_does_nothing_when_not_authenticated(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(false);

        $builder = Mockery::mock(Builder::class);
        $builder->shouldNotReceive('where');

        $model = Mockery::mock(Model::class);

        $scope = new UserScope;
        $scope->apply($builder, $model);

        // If no exception and no where call, test passes.
        $this->assertTrue(true);
    }

    public function test_apply_adds_where_when_authenticated(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('id')->once()->andReturn(99);

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('where')
            ->once()
            ->with('user_id', 99)
            ->andReturnSelf();

        $model = Mockery::mock(Model::class);

        $scope = new UserScope;
        $scope->apply($builder, $model);

        $this->assertTrue(true);
    }
}
