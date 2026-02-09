<?php

namespace Tests\Feature\Api;

use App\Enums\PlanAssignmentStatus;
use App\Enums\WorkoutStatus;
use App\Models\Tenant;
use App\Models\Tenant\Exercise;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentPlanAssignment;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\Workout;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WorkoutApiParityTest extends TestCase
{
    public function test_update_workout_awards_xp_and_persists_live_progress(): void
    {
        $context = $this->createWorkoutContext(WorkoutStatus::IN_PROGRESS);
        $headers = $this->apiHeaders($context['token'], $context['tenant']->id);

        $response = $this
            ->withHeaders($headers)
            ->patchJson('/api/workouts/' . $context['workout']->id, [
                'exercises' => [
                    [
                        'exercise_id' => $context['exercise']->id,
                        'completed' => true,
                        'sets' => [
                            ['reps' => 10, 'completed' => true],
                        ],
                    ],
                ],
                'elapsed_minutes' => 14,
                'effort' => 7,
            ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Workout updated');
        $response->assertJsonPath('gamification.events.0.awarded', true);
        $response->assertJsonPath('gamification.events.0.exercise_id', $context['exercise']->id);
        $response->assertJsonPath('gamification.events.0.xp_gained', 10);
        $response->assertJsonPath('sync.exercises_updated', true);
        $response->assertJsonPath('sync.live_progress_updated', true);

        tenancy()->initialize($context['tenant']);
        try {
            $this->assertDatabaseHas('exercise_completion_logs', [
                'student_id' => $context['student']->id,
                'exercise_id' => $context['exercise']->id,
            ]);

            $this->assertDatabaseHas('student_gamification_profiles', [
                'student_id' => $context['student']->id,
                'total_xp' => 10,
                'total_exercises_completed' => 1,
            ]);

            $workout = Workout::findOrFail($context['workout']->id);
            $this->assertSame(14, (int) data_get($workout->meta, 'live_elapsed_minutes'));
            $this->assertSame(7, (int) data_get($workout->meta, 'live_effort'));
        } finally {
            tenancy()->end();
        }
    }

    public function test_complete_workout_can_store_weight_entry(): void
    {
        $context = $this->createWorkoutContext(WorkoutStatus::IN_PROGRESS);
        $headers = $this->apiHeaders($context['token'], $context['tenant']->id);

        $response = $this
            ->withHeaders($headers)
            ->postJson('/api/workouts/' . $context['workout']->id . '/complete', [
                'duration_minutes' => 38,
                'rating' => 4,
                'notes' => 'Sesion correcta',
                'survey' => [
                    'effort' => 7,
                    'fatigue' => 3,
                    'rpe' => 8,
                ],
                'current_weight' => 79.4,
            ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Workout completed');
        $response->assertJsonPath('data.status', WorkoutStatus::COMPLETED->value);
        $response->assertJsonPath('weight_entry.weight_kg', 79.4);
        $response->assertJsonPath('weight_entry.source', 'workout_completion');

        tenancy()->initialize($context['tenant']);
        try {
            $this->assertDatabaseHas('workouts', [
                'id' => $context['workout']->id,
                'status' => WorkoutStatus::COMPLETED->value,
                'duration_minutes' => 38,
            ]);

            $this->assertDatabaseHas('student_weight_entries', [
                'student_id' => $context['student']->id,
                'source' => 'workout_completion',
            ]);
        } finally {
            tenancy()->end();
        }
    }

    public function test_home_endpoint_includes_gamification_data(): void
    {
        $context = $this->createWorkoutContext(WorkoutStatus::PENDING);
        $headers = $this->apiHeaders($context['token'], $context['tenant']->id);

        $response = $this
            ->withHeaders($headers)
            ->getJson('/api/home');

        $response->assertOk();
        $response->assertJsonPath('data.gamification.has_profile', true);
        $response->assertJsonPath('data.student.gamification.has_profile', true);
    }

    /**
     * @return array{
     *   tenant: Tenant,
     *   token: string,
     *   user: User,
     *   student: Student,
     *   exercise: Exercise,
     *   workout: Workout
     * }
     */
    private function createWorkoutContext(WorkoutStatus $workoutStatus): array
    {
        Notification::fake();

        $tenant = $this->actingAsTenant();

        $user = User::factory()->create([
            'email' => 'student.api@example.com',
            'name' => 'Student Api',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'status' => 'active',
            'email' => $user->email,
            'first_name' => 'Student',
            'last_name' => 'Api',
            'phone' => null,
            'goal' => 'consistency',
            'is_user_enabled' => true,
            'billing_frequency' => 'monthly',
            'account_status' => 'on_time',
            'data' => [
                'training' => [
                    'monthly_goal' => 12,
                ],
            ],
        ]);

        $exercise = Exercise::create([
            'name' => 'Push Up',
            'description' => 'Bodyweight push up',
            'category' => 'upper',
            'level' => 'beginner',
            'equipment' => 'bodyweight',
            'is_active' => true,
        ]);

        $plan = TrainingPlan::create([
            'name' => 'Plan API',
            'description' => 'Plan para tests API',
            'goal' => 'consistency',
            'duration' => '4 weeks',
            'is_active' => true,
            'meta' => ['version' => 1.0],
        ]);

        $assignment = StudentPlanAssignment::create([
            'student_id' => $student->id,
            'training_plan_id' => $plan->id,
            'name' => $plan->name,
            'meta' => ['version' => 1.0, 'origin' => 'test'],
            'exercises_snapshot' => [
                [
                    'exercise_id' => $exercise->id,
                    'name' => $exercise->name,
                    'day' => 1,
                    'order' => 1,
                    'sets' => [
                        ['reps' => 10, 'completed' => false],
                    ],
                ],
            ],
            'status' => PlanAssignmentStatus::ACTIVE,
            'is_active' => true,
            'starts_at' => now()->subDays(1)->toDateString(),
            'ends_at' => now()->addDays(20)->toDateString(),
        ]);

        $workout = Workout::create([
            'student_id' => $student->id,
            'student_plan_assignment_id' => $assignment->id,
            'plan_day' => 1,
            'sequence_index' => 1,
            'cycle_index' => 1,
            'status' => $workoutStatus,
            'started_at' => $workoutStatus === WorkoutStatus::IN_PROGRESS ? now()->subMinutes(5) : null,
            'exercises_data' => [
                [
                    'exercise_id' => $exercise->id,
                    'name' => $exercise->name,
                    'completed' => false,
                    'sets' => [
                        ['reps' => 10, 'completed' => false],
                    ],
                ],
            ],
            'meta' => [],
        ]);

        $token = $user->createToken('api-test-token')->plainTextToken;

        tenancy()->end();

        return [
            'tenant' => $tenant,
            'token' => $token,
            'user' => $user,
            'student' => $student,
            'exercise' => $exercise,
            'workout' => $workout,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function apiHeaders(string $token, string $tenantId): array
    {
        return [
            'Authorization' => 'Bearer ' . $token,
            'X-Tenant-ID' => $tenantId,
        ];
    }
}
