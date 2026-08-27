<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Figure;
use App\Models\Timeline;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DummyHasMany extends HasMany
{
    public string $relatedClass = '';

    public string $foreignKeyName = '';

    public function getRelatedClass(): string
    {
        return $this->relatedClass;
    }
}

class TimelineTest extends TestCase
{
    public function test_casts_method_returns_array(): void
    {
        $timeline = new Timeline;

        $expected = [
            'part_one' => 'array',
            'part_two' => 'array',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];

        $this->assertSame($expected, $timeline->casts());
    }

    public function test_get_fillable_contains_expected_fields(): void
    {
        $timeline = new Timeline;

        $this->assertSame([
            'locale',
            'slug',
            'title',
            'tldr',
            'part_one',
            'part_two',
            'translation_key',
        ], $timeline->getFillable());
    }

    public function test_figures_relation_returns_has_many(): void
    {
        $timeline = $this->timelineWithFakeHasMany();

        $relation = $timeline->figures();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertSame(Figure::class, $relation->getRelatedClass());
    }

    public function test_translations_relation_returns_has_many_on_translation_key(): void
    {
        $timeline = $this->timelineWithFakeHasMany();

        $relation = $timeline->translations();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertSame(Timeline::class, $relation->getRelatedClass());
        $this->assertSame('translation_key', $relation->foreignKeyName);
    }

    public function test_translation_returns_other_locale_row(): void
    {
        $timeline = new Timeline;
        $timeline->locale = 'en';
        $timeline->slug = 'a-slug';
        $timeline->translation_key = 'a-slug';

        $other = new Timeline;
        $other->locale = 'sr';
        $other->slug = 'neki-slag';
        $other->translation_key = 'a-slug';

        $timeline->setRelation('translations', new Collection([$timeline, $other]));

        $this->assertSame($other, $timeline->translation());
    }

    public function test_translation_returns_null_when_missing(): void
    {
        $timeline = new Timeline;
        $timeline->locale = 'en';
        $timeline->translation_key = 'a-slug';

        $timeline->setRelation('translations', new Collection([$timeline]));

        $this->assertNull($timeline->translation());
    }

    private function timelineWithFakeHasMany(): Timeline
    {
        return new class extends Timeline
        {
            public function hasMany($related, $foreignKey = null, $localKey = null)
            {
                $ref = new ReflectionClass(DummyHasMany::class);

                $obj = $ref->newInstanceWithoutConstructor();

                $obj->relatedClass = $related;
                $obj->foreignKeyName = (string) $foreignKey;

                return $obj;
            }
        };
    }
}
