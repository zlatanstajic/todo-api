<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Todo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * TodoFactory
 *
 * Factory for creating Todo model instances for testing and seeding.
 */
class TodoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Todo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 2),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'completed' => $this->faker->boolean(20),
        ];
    }
}
