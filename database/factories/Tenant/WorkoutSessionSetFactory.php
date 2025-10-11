<?php

namespace Database\Factories\Tenant;

use App\Models\Tenant\WorkoutSessionSet;
use App\Models\Tenant\WorkoutSession;
use App\Models\Tenant\Exercise\ExercisePlanItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkoutSessionSetFactory extends Factory
{
    protected $model = WorkoutSessionSet::class;

    public function definition(): array
    {
        return [
            'workout_session_id' => WorkoutSession::factory(),
            'plan_item_id' => ExercisePlanItem::inRandomOrder()->value('id'),
            'set_number' => $this->faker->numberBetween(1, 5),

            'target_load' => $this->faker->optional()->randomFloat(1, 20, 100),
            'target_reps' => $this->faker->optional()->numberBetween(6, 15),
            'target_rest_sec' => $this->faker->optional()->numberBetween(30, 90),

            'actual_load' => $this->faker->optional()->randomFloat(1, 20, 100),
            'actual_reps' => $this->faker->optional()->numberBetween(6, 15),
            'actual_rest_sec' => $this->faker->optional()->numberBetween(30, 90),

            'rpe' => $this->faker->optional()->numberBetween(6, 9),
            'notes' => $this->faker->optional()->sentence(),
            'completed_at' => $this->faker->optional()->dateTimeBetween('-2 hours'),
            'meta' => [
                'feedback' => $this->faker->randomElement(['good', 'fatigued', 'excellent']),
            ],
        ];
    }
}
