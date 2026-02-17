<?php

namespace Database\Factories\Tenant;

use App\Models\Tenant\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant\Exercise>
 */
class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::orderedUuid(),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->optional()->sentence(6),
            'category' => $this->faker->optional()->word(),
            'level' => $this->faker->optional()->randomElement(['beginner', 'intermediate', 'advanced']),
            'equipment' => $this->faker->optional()->word(),
            'is_active' => true,
            'created_by_ai' => false,
            'meta' => [],
        ];
    }
}
