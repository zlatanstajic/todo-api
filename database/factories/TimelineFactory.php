<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Timeline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * TimelineFactory
 *
 * Factory for creating Timeline model instances for testing and seeding.
 */
class TimelineFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Timeline::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = $this->faker->unique()->slug();

        return [
            'locale' => 'en',
            'slug' => $slug,
            'title' => $this->faker->sentence(4),
            'tldr' => $this->faker->optional()->paragraph(),
            'part_one' => [$this->faker->paragraph(), $this->faker->paragraph()],
            'part_two' => [$this->faker->paragraph(), $this->faker->paragraph()],
            'translation_key' => $slug,
        ];
    }
}
