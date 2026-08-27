<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Figure;
use App\Models\Timeline;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * FigureFactory
 *
 * Factory for creating Figure model instances for testing and seeding.
 */
class FigureFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Figure::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->name();

        return [
            'timeline_id' => Timeline::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
