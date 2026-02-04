<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Scopes\UserScope;
use App\Models\Todo;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DummyBelongsTo extends BelongsTo
{
    public string $relatedClass = '';

    public function getRelatedClass(): string
    {
        return $this->relatedClass;
    }
}

class TodoTest extends TestCase
{
    public function test_casts_method_returns_array(): void
    {
        $todo = new Todo;

        $closure = $todo->casts(...);

        $casts = $closure();

        $expected = [
            'completed' => 'boolean',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
        ];

        $this->assertIsArray($casts);
        $this->assertSame($expected, $casts);
    }

    public function test_booted_adds_user_scope(): void
    {
        $boot = Closure::bind(Todo::booted(...), null, Todo::class);

        $boot();

        $get = Closure::bind(fn () => EloquentModel::$globalScopes, null, EloquentModel::class);

        $all = $get();

        $todoScopes = $all[Todo::class] ?? [];

        $found = false;
        foreach ($todoScopes as $scope) {
            if ($scope instanceof UserScope) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);

        $set = Closure::bind(function ($v): void {
            EloquentModel::$globalScopes = $v;
        }, null, EloquentModel::class);

        $set([]);
    }

    public function test_user_relation_returns_belongs_to(): void
    {
        $todo = new class extends Todo
        {
            public function belongsTo($related, $foreignKey = null,
                $ownerKey = null, $relation = null
            ) {
                $ref = new ReflectionClass(DummyBelongsTo::class);

                $obj = $ref->newInstanceWithoutConstructor();

                $obj->relatedClass = $related;

                return $obj;
            }
        };

        $relation = $todo->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);

        $this->assertSame(User::class, $relation->getRelatedClass());
    }

    public function test_get_fillable_contains_expected_fields(): void
    {
        $todo = new Todo;

        $fillable = $todo->getFillable();

        $this->assertSame([
            'user_id',
            'title',
            'description',
            'completed',
        ], $fillable);
    }
}
