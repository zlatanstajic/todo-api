<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Resources\PersonResource;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class PersonResourceTest extends TestCase
{
    public function test_to_array_exposes_expected_attributes(): void
    {
        $person = [
            'slug' => 'nikola-tesla',
            'locale' => 'en',
            'name' => 'Nikola Tesla',
            'timelines' => [
                ['slug' => 'timeline-a', 'title' => 'Timeline A', 'description' => 'Inventor.'],
            ],
        ];

        $resource = new PersonResource($person);

        $result = $resource->toArray(new Request);

        $this->assertSame('person', $result['type']);
        $this->assertSame('nikola-tesla', $result['id']);
        $this->assertSame([
            'slug' => 'nikola-tesla',
            'locale' => 'en',
            'name' => 'Nikola Tesla',
            'timelines' => [
                ['slug' => 'timeline-a', 'title' => 'Timeline A', 'description' => 'Inventor.'],
            ],
        ], $result['attributes']);
    }
}
