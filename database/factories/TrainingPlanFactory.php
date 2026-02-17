<?php

namespace Database\Factories;

use App\Models\Tenant\TrainingPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TrainingPlanFactory extends Factory
{
    protected $model = TrainingPlan::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::orderedUuid(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->sentence(6),
            'goal' => $this->faker->optional()->word(),
            'duration' => $this->faker->randomElement(['4 weeks', '8 weeks', '12 weeks']),
            'is_active' => true,
            'meta' => [
                'version' => 1.0,
                'origin' => 'factory',
            ],
            'student_id' => null,
            'assigned_from' => null,
            'assigned_until' => null,
            'exercises_data' => [],
            'created_by_ai' => false,
        ];
    }
}
