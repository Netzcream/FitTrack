<?php

namespace Database\Factories\Tenant;

use App\Enums\WorkoutStatus;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentPlanAssignment;
use App\Models\Tenant\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant\Workout>
 */
class WorkoutFactory extends Factory
{
    protected $model = Workout::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::orderedUuid(),
            'session_instance_id' => (string) Str::orderedUuid(),
            'student_id' => Student::factory(),
            'student_plan_assignment_id' => StudentPlanAssignment::factory(),
            'plan_day' => 1,
            'sequence_index' => 0,
            'cycle_index' => 1,
            'started_at' => now()->subMinutes(60),
            'completed_at' => now(),
            'duration_minutes' => 60,
            'status' => WorkoutStatus::COMPLETED,
            'rating' => 4,
            'notes' => $this->faker->optional()->sentence(),
            'exercises_data' => [],
            'meta' => [],
        ];
    }
}
