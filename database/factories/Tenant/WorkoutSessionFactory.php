<?php

namespace Database\Factories\Tenant;

use App\Models\Tenant\WorkoutSession;
use App\Models\Tenant\Student;
use App\Models\Tenant\Exercise\ExercisePlanWorkout;
use App\Models\Tenant\Exercise\ExercisePlanBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkoutSessionFactory extends Factory
{
    protected $model = WorkoutSession::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::inRandomOrder()->value('id') ?? Student::factory(),
            'plan_workout_id' => ExercisePlanWorkout::inRandomOrder()->value('id'),
            'plan_block_id' => ExercisePlanBlock::inRandomOrder()->value('id'),
            'scheduled_date' => $this->faker->dateTimeBetween('-1 week', '+1 week'),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed']),
            'started_at' => $this->faker->optional()->dateTimeBetween('-2 hours'),
            'ended_at' => $this->faker->optional()->dateTimeBetween('-1 hours'),
            'duration_minutes' => $this->faker->optional()->numberBetween(20, 90),
            'session_rpe' => $this->faker->optional()->numberBetween(5, 9),
            'meta' => [
                'device' => $this->faker->randomElement(['mobile', 'web']),
                'auto_generated' => true,
            ],
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn() => ['status' => 'completed', 'ended_at' => now()]);
    }
}
