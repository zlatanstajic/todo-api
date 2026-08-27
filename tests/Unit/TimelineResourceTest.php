<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Resources\TimelineResource;
use App\Models\Figure;
use App\Models\Timeline;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class TimelineResourceTest extends TestCase
{
    public function test_to_array_exposes_expected_attributes(): void
    {
        $timeline = new Timeline;
        $timeline->id = 11;
        $timeline->locale = 'en';
        $timeline->slug = 'a-slug';
        $timeline->title = 'A Title';
        $timeline->tldr = 'Short summary.';
        $timeline->part_one = ['First line.'];
        $timeline->part_two = ['Second line.'];
        $timeline->translation_key = 'a-slug';

        $translation = new Timeline;
        $translation->locale = 'sr';
        $translation->slug = 'srpski-slag';
        $translation->translation_key = 'a-slug';

        $figure = new Figure;
        $figure->name = 'Some Person';
        $figure->slug = 'some-person';
        $figure->description = 'A description.';

        $timeline->setRelation('figures', new Collection([$figure]));
        $timeline->setRelation('translations', new Collection([$timeline, $translation]));

        $resource = new TimelineResource($timeline);

        $result = $resource->toArray(new Request);

        $this->assertSame(Timeline::class, $result['type']);
        $this->assertSame(11, $result['id']);
        $this->assertSame([
            'locale' => 'en',
            'slug' => 'a-slug',
            'title' => 'A Title',
            'tldr' => 'Short summary.',
            'part_one' => ['First line.'],
            'part_two' => ['Second line.'],
            'figures' => [
                [
                    'name' => 'Some Person',
                    'slug' => 'some-person',
                    'description' => 'A description.',
                ],
            ],
            'translation_slug' => 'srpski-slag',
            'created_at' => null,
            'updated_at' => null,
        ], $result['attributes']);
    }

    public function test_to_array_handles_missing_translation(): void
    {
        $timeline = new Timeline;
        $timeline->id = 12;
        $timeline->locale = 'en';
        $timeline->translation_key = 'a-slug';

        $timeline->setRelation('figures', new Collection([]));
        $timeline->setRelation('translations', new Collection([$timeline]));

        $resource = new TimelineResource($timeline);

        $result = $resource->toArray(new Request);

        $this->assertNull($result['attributes']['translation_slug']);
        $this->assertSame([], $result['attributes']['figures']);
    }
}
