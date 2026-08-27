<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Figure;
use App\Models\Timeline;
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

class FigureTest extends TestCase
{
    public function test_casts_method_returns_array(): void
    {
        $figure = new Figure;

        $expected = [
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];

        $this->assertSame($expected, $figure->casts());
    }

    public function test_get_fillable_contains_expected_fields(): void
    {
        $figure = new Figure;

        $this->assertSame([
            'timeline_id',
            'name',
            'slug',
            'description',
        ], $figure->getFillable());
    }

    public function test_timeline_relation_returns_belongs_to(): void
    {
        $figure = new class extends Figure
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

        $relation = $figure->timeline();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertSame(Timeline::class, $relation->getRelatedClass());
    }
}
