<?php

namespace Tests\Feature\Api;

use App\Enums\PlanAssignmentStatus;
use App\Enums\WorkoutStatus;
use App\Models\Tenant;
use App\Models\Tenant\Exercise;
use App\Models\Tenant\ExerciseCompletionLog;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentPlanAssignment;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\Workout;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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
                'elapsed_seconds' => 845,
                'effort' => 7,
            ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Workout updated');
        $response->assertJsonPath('gamification.events.0.awarded', true);
        $response->assertJsonPath('gamification.events.0.exercise_id', $context['exercise']->id);
        $response->assertJsonPath('gamification.events.0.xp_gained', 10);
        $response->assertJsonPath('sync.exercises_updated', true);
        $response->assertJsonPath('sync.live_progress_updated', true);
        $response->assertJsonPath('context.student.uuid', $context['student']->uuid);
        $response->assertJsonPath('context.active_plan.uuid', $context['assignment']->uuid);
        $response->assertJsonPath('data.exercises.0.xp_base_value', 10);
        $response->assertJsonPath('data.exercises.0.xp.base_value', 10);
        $response->assertJsonPath('data.exercises.0.xp_to_award_if_complete_now', 0);
        $response->assertJsonPath('data.exercises.0.xp.to_award_if_complete_now', 0);
        $response->assertJsonPath('data.exercises_data.0.xp.base_value', 10);
        $response->assertJsonPath('data.exercises_data.0.xp_to_award_if_complete_now', 0);
        $response->assertJsonPath('data.progress.completed_exercises', 1);
        $response->assertJsonPath('data.progress.total_sets', 1);
        $response->assertJsonPath('data.progress.completed_sets', 1);
        $response->assertJsonPath('data.progress.remaining_sets', 0);
        $response->assertJsonPath('data.progress.session_state', 'active');
        $response->assertJsonPath('data.exercise_progress.0.sets_total', 1);
        $response->assertJsonPath('data.exercise_progress.0.sets_completed', 1);
        $response->assertJsonPath('data.exercise_progress.0.xp_to_award_if_complete_now', 0);
        $response->assertJsonPath('data.live.elapsed_minutes', 14);
        $response->assertJsonPath('data.live.elapsed_seconds', 845);
        $response->assertJsonPath('data.live.effort', 7);
        $response->assertJsonPath('data.physical_activity.timer.elapsed_seconds', 845);
        $response->assertJsonPath('data.physical_activity.effort.value', 7);
        $response->assertJsonPath('data.physical_activity.exercise_progress.all_completed', true);
        $response->assertJsonPath('data.xp_summary.available_to_earn_now_total', 0);
        $response->assertJsonPath('data.xp_summary.already_awarded_in_session_total', 10);
        $response->assertJsonPath('data.assignment.uuid', $context['assignment']->uuid);
        $response->assertJsonStructure([
            'data' => [
                'live' => ['last_sync_at'],
                'physical_activity' => [
                    'timer' => ['last_sync_at'],
                ],
            ],
        ]);

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
            $this->assertSame(845, (int) data_get($workout->meta, 'live_elapsed_seconds'));
            $this->assertSame(7, (int) data_get($workout->meta, 'live_effort'));
            $this->assertNotNull(data_get($workout->meta, 'live_last_sync_at'));
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
        $response->assertJsonPath('context.requested_workout_uuid', $context['workout']->uuid);

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

    public function test_same_exercise_awards_xp_again_in_new_session_same_day(): void
    {
        $context = $this->createWorkoutContext(WorkoutStatus::IN_PROGRESS);
        $headers = $this->apiHeaders($context['token'], $context['tenant']->id);

        $payload = [
            'exercises' => [
                [
                    'exercise_id' => $context['exercise']->id,
                    'completed' => true,
                    'sets' => [
                        ['reps' => 10, 'completed' => true],
                    ],
                ],
            ],
        ];

        $sessionAResponse = $this
            ->withHeaders($headers)
            ->patchJson('/api/workouts/' . $context['workout']->id, $payload);

        $sessionAResponse->assertOk();
        $sessionAResponse->assertJsonPath('gamification.events.0.reason', 'awarded');
        $sessionAResponse->assertJsonPath('gamification.events.0.awarded_xp', 10);
        $sessionAResponse->assertJsonPath('gamification.profile.total_xp', 10);

        tenancy()->initialize($context['tenant']);
        try {
            $workoutB = Workout::create([
                'student_id' => $context['student']->id,
                'student_plan_assignment_id' => $context['assignment']->id,
                'plan_day' => 1,
                'sequence_index' => 2,
                'cycle_index' => 1,
                'status' => WorkoutStatus::IN_PROGRESS,
                'started_at' => now(),
                'exercises_data' => [
                    [
                        'exercise_id' => $context['exercise']->id,
                        'name' => $context['exercise']->name,
                        'completed' => false,
                        'sets' => [
                            ['reps' => 10, 'completed' => false],
                        ],
                    ],
                ],
                'meta' => [],
            ]);
        } finally {
            tenancy()->end();
        }

        $sessionBResponse = $this
            ->withHeaders($headers)
            ->patchJson('/api/workouts/' . $workoutB->id, $payload);

        $sessionBResponse->assertOk();
        $sessionBResponse->assertJsonPath('gamification.events.0.reason', 'awarded');
        $sessionBResponse->assertJsonPath('gamification.events.0.awarded_xp', 10);
        $sessionBResponse->assertJsonPath('gamification.profile.total_xp', 20);

        tenancy()->initialize($context['tenant']);
        try {
            $logs = ExerciseCompletionLog::query()
                ->where('student_id', $context['student']->id)
                ->where('exercise_id', $context['exercise']->id)
                ->orderBy('id')
                ->get();

            $this->assertCount(2, $logs);
            $this->assertNotSame($logs[0]->session_instance_id, $logs[1]->session_instance_id);
        } finally {
            tenancy()->end();
        }
    }

    public function test_retry_in_same_session_returns_already_awarded_in_session(): void
    {
        $context = $this->createWorkoutContext(WorkoutStatus::IN_PROGRESS);
        $headers = $this->apiHeaders($context['token'], $context['tenant']->id);

        $completedPayload = [
            'exercises' => [
                [
                    'exercise_id' => $context['exercise']->id,
                    'completed' => true,
                    'sets' => [
                        ['reps' => 10, 'completed' => true],
                    ],
                ],
            ],
        ];

        $firstResponse = $this
            ->withHeaders($headers)
            ->patchJson('/api/workouts/' . $context['workout']->id, $completedPayload);

        $firstResponse->assertOk();
        $firstResponse->assertJsonPath('gamification.events.0.reason', 'awarded');
        $firstResponse->assertJsonPath('gamification.events.0.awarded_xp', 10);
        $sessionInstanceId = $firstResponse->json('gamification.events.0.session_instance_id');

        $this
            ->withHeaders($headers)
            ->patchJson('/api/workouts/' . $context['workout']->id, [
                'exercises' => [
                    [
                        'exercise_id' => $context['exercise']->id,
                        'completed' => false,
                        'sets' => [
                            ['reps' => 10, 'completed' => false],
                        ],
                    ],
                ],
            ])
            ->assertOk();

        $retryResponse = $this
            ->withHeaders($headers)
            ->patchJson('/api/workouts/' . $context['workout']->id, $completedPayload);

        $retryResponse->assertOk();
        $retryResponse->assertJsonPath('gamification.events.0.reason', 'already_awarded_in_session');
        $retryResponse->assertJsonPath('gamification.events.0.awarded_xp', 0);
        $retryResponse->assertJsonPath('gamification.events.0.xp_gained', 0);
        $retryResponse->assertJsonPath('gamification.profile.total_xp', 10);

        tenancy()->initialize($context['tenant']);
        try {
            $this->assertDatabaseCount('exercise_completion_logs', 1);

            if (is_string($sessionInstanceId) && $sessionInstanceId !== '') {
                $this->assertDatabaseHas('exercise_completion_logs', [
                    'student_id' => $context['student']->id,
                    'exercise_id' => $context['exercise']->id,
                    'session_instance_id' => $sessionInstanceId,
                ]);
            }
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
        $response->assertJsonPath('data.has_pending_payment', false);
        $response->assertJsonPath('data.pending_payment', null);
        $response->assertJsonPath('data.home_state.has_active_plan', true);
        $response->assertJsonPath('data.home_state.has_workout_today', true);
        $response->assertJsonPath('data.today_workout.exercises.0.xp_base_value', 10);
        $response->assertJsonPath('data.today_workout.exercises.0.xp_to_award_if_complete_now', 10);
        $response->assertJsonPath('data.today_workout.exercises_data.0.xp_to_award_if_complete_now', 10);
        $response->assertJsonPath('data.today_workout.progress.total_sets', 1);
        $response->assertJsonPath('data.today_workout.physical_activity.exercise_progress.all_completed', false);
    }

    public function test_show_workout_includes_live_progress_and_per_exercise_xp_contract(): void
    {
        $context = $this->createWorkoutContext(WorkoutStatus::IN_PROGRESS);
        $headers = $this->apiHeaders($context['token'], $context['tenant']->id);

        tenancy()->initialize($context['tenant']);
        try {
            $workout = Workout::findOrFail($context['workout']->id);
            $workout->update([
                'meta' => [
                    'live_elapsed_minutes' => 5,
                    'live_elapsed_seconds' => 305,
                    'live_effort' => 6,
                    'live_last_sync_at' => now()->toIso8601String(),
                ],
            ]);
        } finally {
            tenancy()->end();
        }

        $response = $this
            ->withHeaders($headers)
            ->getJson('/api/workouts/' . $context['workout']->id);

        $response->assertOk();
        $response->assertJsonPath('data.exercises.0.xp_base_value', 10);
        $response->assertJsonPath('data.exercises.0.xp_to_award_if_complete_now', 10);
        $response->assertJsonPath('data.exercises_data.0.xp.base_value', 10);
        $response->assertJsonPath('data.exercises_data.0.xp_to_award_if_complete_now', 10);
        $response->assertJsonPath('data.exercise_progress.0.xp_to_award_if_complete_now', 10);
        $response->assertJsonPath('data.progress.total_exercises', 1);
        $response->assertJsonPath('data.progress.completed_exercises', 0);
        $response->assertJsonPath('data.progress.total_sets', 1);
        $response->assertJsonPath('data.progress.completed_sets', 0);
        $response->assertJsonPath('data.live.elapsed_minutes', 5);
        $response->assertJsonPath('data.live.elapsed_seconds', 305);
        $response->assertJsonPath('data.live.effort', 6);
        $response->assertJsonPath('data.physical_activity.timer.elapsed_minutes', 5);
        $response->assertJsonPath('data.physical_activity.timer.elapsed_seconds', 305);
        $response->assertJsonPath('data.physical_activity.effort.value', 6);
        $response->assertJsonPath('data.physical_activity.set_progress.total', 1);
        $response->assertJsonPath('data.physical_activity.set_progress.completed', 0);
        $response->assertJsonPath('data.xp_summary.base_total_if_all_exercises_rewarded', 10);
        $response->assertJsonPath('data.xp_summary.available_to_earn_now_total', 10);
    }

    public function test_show_workout_returns_full_images_collection_from_exercise_media(): void
    {
        $context = $this->createWorkoutContext(WorkoutStatus::IN_PROGRESS);
        $headers = $this->apiHeaders($context['token'], $context['tenant']->id);

        tenancy()->initialize($context['tenant']);
        try {
            $disk = (string) config('media-library.disk_name', 'public');
            Storage::fake($disk);

            $exercise = Exercise::findOrFail($context['exercise']->id);
            $exercise->addMedia(UploadedFile::fake()->image('exercise-1.jpg'))->toMediaCollection('images');
            $exercise->addMedia(UploadedFile::fake()->image('exercise-2.jpg'))->toMediaCollection('images');

            $workout = Workout::findOrFail($context['workout']->id);
            $workout->update([
                'exercises_data' => [
                    [
                        'exercise_id' => $exercise->id,
                        'name' => $exercise->name,
                        'completed' => false,
                        'image_url' => $exercise->getFirstMediaUrl('images'),
                        'sets' => [
                            ['reps' => 10, 'completed' => false],
                        ],
                    ],
                ],
            ]);
        } finally {
            tenancy()->end();
        }

        $response = $this
            ->withHeaders($headers)
            ->getJson('/api/workouts/' . $context['workout']->id);

        $response->assertOk();

        $images = $response->json('data.exercises.0.images');
        $this->assertIsArray($images);
        $this->assertCount(2, $images);
        $this->assertNotEmpty($images[0]['url'] ?? null);
        $this->assertNotEmpty($images[1]['url'] ?? null);
        $this->assertSame($images[0]['url'], $response->json('data.exercises.0.image_url'));
    }

    public function test_show_workout_hydrates_description_from_exercise_when_payload_description_is_null(): void
    {
        $context = $this->createWorkoutContext(WorkoutStatus::IN_PROGRESS);
        $headers = $this->apiHeaders($context['token'], $context['tenant']->id);

        tenancy()->initialize($context['tenant']);
        try {
            $exercise = Exercise::findOrFail($context['exercise']->id);
            $exercise->update([
                'description' => 'Empuja la barra en banco plano con control',
                'category' => 'Pecho',
                'level' => 'intermediate',
                'equipment' => 'barra',
            ]);

            $workout = Workout::findOrFail($context['workout']->id);
            $workout->update([
                'exercises_data' => [
                    [
                        'exercise_id' => $exercise->id,
                        'name' => $exercise->name,
                        'day' => 1,
                        'order' => 1,
                        'detail' => '5x10',
                        'notes' => 'Ritmo 2-1-2',
                        'meta' => ['source' => 'seed'],
                        'description' => null,
                        'category' => 'Pecho',
                        'level' => 'intermediate',
                        'completed' => false,
                        'sets' => [
                            ['reps' => 10, 'completed' => false],
                        ],
                    ],
                ],
            ]);
        } finally {
            tenancy()->end();
        }

        $response = $this
            ->withHeaders($headers)
            ->getJson('/api/workouts/' . $context['workout']->id);

        $response->assertOk();
        $response->assertJsonPath(
            'data.exercises.0.description',
            'Empuja la barra en banco plano con control'
        );
        $response->assertJsonPath(
            'data.exercises_data.0.description',
            'Empuja la barra en banco plano con control'
        );
    }

    /**
     * @return array{
     *   tenant: Tenant,
     *   token: string,
     *   user: User,
     *   student: Student,
     *   exercise: Exercise,
     *   assignment: StudentPlanAssignment,
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
            'assignment' => $assignment,
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
