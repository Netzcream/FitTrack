<?php

namespace Database\Factories;

use App\Models\Tenant\StudentPlanAssignment;
use App\Models\Tenant\Student;
use App\Models\Tenant\TrainingPlan;
use App\Enums\PlanAssignmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StudentPlanAssignmentFactory extends Factory
{
    protected $model = StudentPlanAssignment::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::orderedUuid(),
            'student_id' => Student::factory(),
            'training_plan_id' => TrainingPlan::factory(),
            'name' => $this->faker->sentence(3),
            'meta' => [
                'version' => 1.0,
                'origin' => 'factory',
            ],
            'exercises_snapshot' => [],
            'status' => PlanAssignmentStatus::ACTIVE,
            'is_active' => true,
            'starts_at' => now()->startOfDay(),
            'ends_at' => now()->addWeeks(4)->startOfDay(),
            'overrides' => [],
        ];
    }
}
