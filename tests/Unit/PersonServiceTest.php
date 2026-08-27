<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Figure;
use App\Models\Timeline;
use App\Repositories\FigureRepository;
use App\Services\PersonService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class PersonServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_get_all_people_groups_figures_by_slug(): void
    {
        $figures = new Collection([
            $this->makeFigure('Nikola Tesla', 'nikola-tesla', 'Inventor.', 'timeline-a', 'Timeline A'),
            $this->makeFigure('Nikola Tesla', 'nikola-tesla', 'Engineer.', 'timeline-b', 'Timeline B'),
            $this->makeFigure('Some General', 'some-general', 'Soldier.', 'timeline-a', 'Timeline A'),
        ]);

        $repo = Mockery::mock(FigureRepository::class);
        $repo->shouldReceive('getAllByLocale')->once()->with('en')
            ->andReturn($figures);

        $service = new PersonService($repo);

        $people = $service->getAllPeople('en');

        $this->assertCount(2, $people);
        $this->assertSame('nikola-tesla', $people[0]['slug']);
        $this->assertSame('en', $people[0]['locale']);
        $this->assertSame('Nikola Tesla', $people[0]['name']);
        $this->assertSame([
            ['slug' => 'timeline-a', 'title' => 'Timeline A', 'description' => 'Inventor.'],
            ['slug' => 'timeline-b', 'title' => 'Timeline B', 'description' => 'Engineer.'],
        ], $people[0]['timelines']);
        $this->assertSame('some-general', $people[1]['slug']);
    }

    public function test_get_person_by_slug_returns_person(): void
    {
        $figures = new Collection([
            $this->makeFigure('Nikola Tesla', 'nikola-tesla', 'Inventor.', 'timeline-a', 'Timeline A'),
        ]);

        $repo = Mockery::mock(FigureRepository::class);
        $repo->shouldReceive('getBySlug')->once()->with('en', 'nikola-tesla')
            ->andReturn($figures);

        $service = new PersonService($repo);

        $person = $service->getPersonBySlug('en', 'nikola-tesla');

        $this->assertNotNull($person);
        $this->assertSame('nikola-tesla', $person['slug']);
        $this->assertSame('Nikola Tesla', $person['name']);
        $this->assertCount(1, $person['timelines']);
    }

    public function test_get_person_by_slug_returns_null_when_missing(): void
    {
        $repo = Mockery::mock(FigureRepository::class);
        $repo->shouldReceive('getBySlug')->once()->with('en', 'unknown')
            ->andReturn(new Collection([]));

        $service = new PersonService($repo);

        $this->assertNull($service->getPersonBySlug('en', 'unknown'));
    }

    private function makeFigure(
        string $name,
        string $slug,
        string $description,
        string $timelineSlug,
        string $timelineTitle
    ): Figure {
        $timeline = new Timeline;
        $timeline->slug = $timelineSlug;
        $timeline->title = $timelineTitle;

        $figure = new Figure;
        $figure->name = $name;
        $figure->slug = $slug;
        $figure->description = $description;
        $figure->setRelation('timeline', $timeline);

        return $figure;
    }
}
