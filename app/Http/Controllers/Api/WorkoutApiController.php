<?php

namespace App\Http\Controllers\Api;

use App\Enums\WorkoutStatus;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Exercise;
use App\Models\Tenant\ExerciseCompletionLog;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentGamificationProfile;
use App\Models\Tenant\StudentWeightEntry;
use App\Models\Tenant\Workout;
use App\Services\Api\WorkoutDataFormatter;
use App\Services\Tenant\GamificationService;
use App\Services\WorkoutOrchestrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WorkoutApiController extends Controller
{
    public function __construct(
        protected WorkoutOrchestrationService $orchestration,
        protected WorkoutDataFormatter $workoutDataFormatter,
        protected GamificationService $gamificationService
    ) {}

    /**
     * GET /api/workouts
     *
     * Listar todos los workouts del estudiante (con filtro por status)
     */
    public function index(Request $request)
    {
        $student = $this->findStudentFromRequest($request);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Filtrar por status si se proporciona
        $status = $request->query('status'); // pending|in_progress|completed|skipped

        $query = $student->workouts()
            ->with('planAssignment')
            ->orderByDesc('created_at');

        if ($status && in_array($status, ['pending', 'in_progress', 'completed', 'skipped'])) {
            $query->where('status', $status);
        }

        $workouts = $query->get();

        return response()->json([
            'data' => $workouts->map(function ($workout) {
                return $this->workoutDataFormatter->format($workout);
            }),
            'context' => $this->buildWorkoutContextPayload($student),
        ]);
    }

    /**
     * GET /api/workouts/{id}
     *
     * Obtener detalles completos de un workout específico
     */
    public function show(Request $request, $id)
    {
        $student = $this->findStudentFromRequest($request);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Acepta ID numerico o UUID
        $workout = $this->findWorkoutForStudent($student, $id)?->loadMissing('planAssignment');

        if (!$workout) {
            return response()->json(['error' => 'Workout not found'], 404);
        }

        return response()->json([
            'data' => $this->workoutDataFormatter->format($workout),
            'context' => $this->buildWorkoutContextPayload($student, $workout),
        ]);
    }

    /**
     * POST /api/workouts/today
     *
     * Obtener o crear el workout de hoy basado en el plan activo
     */
    public function today(Request $request)
    {
        $student = $this->findStudentFromRequest($request);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Resolver plan activo
        $plan = $this->orchestration->resolveActivePlan($student);

        if (!$plan) {
            return response()->json([
                'data' => null,
                'message' => 'No active training plan assigned'
            ]);
        }

        // Obtener o crear workout
        $workout = $this->orchestration->getOrCreateTodayWorkout($student, $plan);

        if (!$workout) {
            return response()->json([
                'error' => 'Failed to create workout'
            ], 500);
        }

        return response()->json([
            'data' => $this->workoutDataFormatter->format($workout),
            'context' => $this->buildWorkoutContextPayload($student, $workout),
        ]);
    }

    /**
     * POST /api/workouts/{id}/start
     *
     * Iniciar un workout (cambiar status a in_progress)
     */
    public function start(Request $request, $id)
    {
        $student = $this->findStudentFromRequest($request);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $workout = $this->findWorkoutForStudent($student, $id);

        if (!$workout) {
            return response()->json(['error' => 'Workout not found'], 404);
        }

        if ($workout->status !== WorkoutStatus::PENDING &&
            $workout->status !== WorkoutStatus::IN_PROGRESS) {
            return response()->json([
                'error' => 'Cannot start a workout with status ' . $workout->status->value
            ], 422);
        }

        $workout->startWorkout();

        return response()->json([
            'message' => 'Workout started',
            'data' => $this->workoutDataFormatter->format($workout),
            'context' => $this->buildWorkoutContextPayload($student, $workout),
        ]);
    }

    /**
     * PATCH /api/workouts/{id}
     *
     * Actualizar datos de ejercicios durante la sesión
     */
    public function update(Request $request, $id)
    {
        $student = $this->findStudentFromRequest($request);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $workout = $this->findWorkoutForStudent($student, $id);

        if (!$workout) {
            return response()->json(['error' => 'Workout not found'], 404);
        }

        // Puede sincronizar ejercicios, progreso en vivo o ambos.
        $validator = Validator::make($request->all(), [
            'exercises' => 'sometimes|array',
            'exercises.*' => 'array',
            'exercises.*.id' => 'nullable',
            'exercises.*.exercise_id' => 'nullable|integer|min:1',
            'exercises.*.name' => 'sometimes|string',
            'exercises.*.completed' => 'sometimes|boolean',
            'exercises.*.sets' => 'sometimes|array',
            'exercises.*.sets.*.reps' => 'sometimes|integer|min:0',
            'exercises.*.sets.*.weight' => 'sometimes|numeric|min:0',
            'exercises.*.sets.*.duration_seconds' => 'sometimes|integer|min:0',
            'exercises.*.sets.*.completed' => 'sometimes|boolean',
            'elapsed_minutes' => 'sometimes|integer|min:0|max:1440',
            'elapsed_seconds' => 'sometimes|integer|min:0|max:86400',
            'effort' => 'sometimes|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid exercise data',
                'details' => $validator->errors()
            ], 422);
        }

        if (!$request->hasAny(['exercises', 'elapsed_minutes', 'elapsed_seconds', 'effort'])) {
            return response()->json([
                'error' => 'Invalid data',
                'details' => [
                    'payload' => ['Provide at least one of: exercises, elapsed_minutes, elapsed_seconds, effort']
                ]
            ], 422);
        }

        $workoutUpdated = false;
        $liveProgressUpdated = false;
        $gamificationEvents = [];

        // 1) Sincronizar ejercicios y otorgar XP si aplica.
        if ($request->has('exercises')) {
            // Garantiza run_id de sesión para deduplicación de XP por sesión.
            $workout->ensureSessionInstanceId();

            $incomingExercises = collect($request->input('exercises', []))
                ->filter(fn ($row) => is_array($row))
                ->values();

            $invalidRows = $incomingExercises
                ->filter(function (array $row): bool {
                    return !array_key_exists('id', $row) && !array_key_exists('exercise_id', $row);
                })
                ->keys()
                ->map(fn (int $index) => $index)
                ->all();

            if (!empty($invalidRows)) {
                return response()->json([
                    'error' => 'Invalid exercise data',
                    'details' => [
                        'exercises' => [
                            'Each exercise must include id or exercise_id. Invalid rows: ' . implode(', ', $invalidRows),
                        ],
                    ],
                ], 422);
            }

            $existingExercises = collect($workout->exercises_data ?? [])->values();
            $consumedIncomingIndexes = [];
            $changedToCompleted = [];

            // Merge preserving existing metadata (description, images, etc.)
            $mergedExercises = $existingExercises
                ->map(function ($existingExercise) use ($incomingExercises, &$consumedIncomingIndexes, &$changedToCompleted) {
                    $existingExercise = is_array($existingExercise) ? $existingExercise : [];

                    $matchIndex = $incomingExercises->search(function ($incomingExercise, int $index) use ($existingExercise, $consumedIncomingIndexes): bool {
                        if (in_array($index, $consumedIncomingIndexes, true)) {
                            return false;
                        }

                        if (!is_array($incomingExercise)) {
                            return false;
                        }

                        return $this->exercisesMatch($existingExercise, $incomingExercise);
                    });

                    if ($matchIndex === false) {
                        return $existingExercise;
                    }

                    $consumedIncomingIndexes[] = $matchIndex;
                    $incomingExercise = $incomingExercises->get($matchIndex, []);
                    $merged = array_merge($existingExercise, $incomingExercise);

                    $wasCompleted = (bool) ($existingExercise['completed'] ?? false);
                    $isCompleted = (bool) ($merged['completed'] ?? false);

                    if (!$wasCompleted && $isCompleted) {
                        $changedToCompleted[] = $merged;
                    }

                    return $merged;
                })
                ->values();

            // Append incoming rows that were not matched.
            $incomingExercises->each(function ($incomingExercise, int $index) use (&$mergedExercises, $consumedIncomingIndexes, &$changedToCompleted): void {
                if (in_array($index, $consumedIncomingIndexes, true) || !is_array($incomingExercise)) {
                    return;
                }

                $mergedExercises->push($incomingExercise);

                if ((bool) ($incomingExercise['completed'] ?? false)) {
                    $changedToCompleted[] = $incomingExercise;
                }
            });

            $workout->updateExercisesData($mergedExercises->toArray());
            $workoutUpdated = true;

            if (!empty($changedToCompleted)) {
                $gamificationEvents = $this->processCompletedExercises($student, $workout, $changedToCompleted);
            }
        }

        // 2) Persistir progreso en vivo (timer + esfuerzo percibido).
        if ($request->has('elapsed_minutes') || $request->has('elapsed_seconds') || $request->has('effort')) {
            $meta = $workout->meta ?? [];

            if ($request->has('elapsed_minutes')) {
                $meta['live_elapsed_minutes'] = (int) $request->input('elapsed_minutes');
                if (!$request->has('elapsed_seconds')) {
                    $meta['live_elapsed_seconds'] = (int) $request->input('elapsed_minutes') * 60;
                }
            }

            if ($request->has('elapsed_seconds')) {
                $elapsedSeconds = (int) $request->input('elapsed_seconds');
                $meta['live_elapsed_seconds'] = $elapsedSeconds;

                if (!$request->has('elapsed_minutes')) {
                    $meta['live_elapsed_minutes'] = (int) floor($elapsedSeconds / 60);
                }
            }

            if ($request->has('effort')) {
                $meta['live_effort'] = (int) $request->input('effort');
            }

            $meta['live_last_sync_at'] = now()->toIso8601String();

            $workout->update(['meta' => $meta]);
            $liveProgressUpdated = true;
        }

        $workout->refresh();

        return response()->json([
            'message' => 'Workout updated',
            'data' => $this->workoutDataFormatter->format($workout),
            'gamification' => [
                'profile' => $this->formatGamificationProfile($student),
                'events' => $gamificationEvents,
            ],
            'sync' => [
                'exercises_updated' => $workoutUpdated,
                'live_progress_updated' => $liveProgressUpdated,
            ],
            'context' => $this->buildWorkoutContextPayload($student, $workout),
        ]);
    }

    /**
     * POST /api/workouts/{id}/complete
     *
     * Completar un workout con duración, rating, notas y survey
     */
    public function complete(Request $request, $id)
    {
        $student = $this->findStudentFromRequest($request);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $workout = $this->findWorkoutForStudent($student, $id);

        if (!$workout) {
            return response()->json(['error' => 'Workout not found'], 404);
        }

        // Validar datos
        $validator = Validator::make($request->all(), [
            'duration_minutes' => 'required|integer|min:1|max:1440',
            'rating' => 'sometimes|integer|min:1|max:5',
            'notes' => 'sometimes|string|max:1000',
            'survey' => 'sometimes|array',
            'survey.effort' => 'sometimes|integer|min:1|max:10',
            'survey.fatigue' => 'integer|min:1|max:5',
            'survey.rpe' => 'integer|min:6|max:20',
            'survey.pain' => 'integer|min:0|max:10',
            'survey.mood' => 'string|max:50',
            'current_weight' => 'sometimes|numeric|min:20|max:300',
            'current_weight_kg' => 'sometimes|numeric|min:20|max:300',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid data',
                'details' => $validator->errors()
            ], 422);
        }

        $workout->completeWorkout(
            durationMinutes: $request->duration_minutes,
            rating: $request->rating,
            notes: $request->notes,
            survey: $request->survey ?? []
        );

        $weightEntry = null;
        $currentWeight = $request->input('current_weight', $request->input('current_weight_kg'));

        if ($currentWeight !== null && $currentWeight !== '') {
            try {
                $weightEntry = StudentWeightEntry::create([
                    'student_id' => $student->id,
                    'weight_kg' => $currentWeight,
                    'source' => 'workout_completion',
                    'recorded_at' => now(),
                    'notes' => 'Recorded when completing workout via API',
                ]);
            } catch (\Throwable $exception) {
                Log::error('Failed to store workout completion weight', [
                    'student_id' => $student->id,
                    'workout_id' => $workout->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $weightPayload = null;
        if ($weightEntry) {
            $weightPayload = [
                'id' => $weightEntry->id,
                'uuid' => $weightEntry->uuid,
                'weight_kg' => (float) $weightEntry->weight_kg,
                'recorded_at' => $weightEntry->recorded_at?->toIso8601String(),
                'source' => $weightEntry->source,
            ];
        }

        return response()->json([
            'message' => 'Workout completed',
            'data' => $this->workoutDataFormatter->format($workout),
            'weight_entry' => $weightPayload,
            'context' => $this->buildWorkoutContextPayload($student, $workout),
        ]);
    }

    /**
     * POST /api/workouts/{id}/skip
     *
     * Saltar un workout
     */
    public function skip(Request $request, $id)
    {
        $student = $this->findStudentFromRequest($request);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $workout = $this->findWorkoutForStudent($student, $id);

        if (!$workout) {
            return response()->json(['error' => 'Workout not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'sometimes|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid data'], 422);
        }

        $workout->skip($request->reason ?? null);

        return response()->json([
            'message' => 'Workout skipped',
            'data' => $this->workoutDataFormatter->format($workout),
            'context' => $this->buildWorkoutContextPayload($student, $workout),
        ]);
    }

    /**
     * GET /api/workouts/stats
     *
     * Obtener estadísticas generales de workouts
     */
    public function stats(Request $request)
    {
        $student = $this->findStudentFromRequest($request);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $completedCount = $student->workouts()
            ->where('status', WorkoutStatus::COMPLETED)
            ->count();

        $pendingCount = $student->workouts()
            ->where('status', WorkoutStatus::PENDING)
            ->count();

        $skippedCount = $student->workouts()
            ->where('status', WorkoutStatus::SKIPPED)
            ->count();

        $averageDuration = $this->orchestration->getAverageDuration($student);
        $averageRating = $this->orchestration->getAverageRating($student);

        return response()->json([
            'data' => [
                'completed_workouts' => $completedCount,
                'pending_workouts' => $pendingCount,
                'skipped_workouts' => $skippedCount,
                'average_duration_minutes' => $averageDuration,
                'average_rating' => $averageRating,
                'total_duration_minutes' => $student->workouts()
                    ->where('status', WorkoutStatus::COMPLETED)
                    ->sum('duration_minutes'),
            ],
            'context' => $this->buildWorkoutContextPayload($student),
        ]);
    }

    private function findStudentFromRequest(Request $request): ?Student
    {
        $user = $request->user();
        if (!$user?->email) {
            return null;
        }

        return Student::where('email', $user->email)->first();
    }

    private function findWorkoutForStudent(Student $student, string|int $identifier): ?Workout
    {
        $identifier = (string) $identifier;

        return Workout::query()
            ->where('student_id', $student->id)
            ->where(function ($query) use ($identifier) {
                if (ctype_digit($identifier)) {
                    $query->orWhere('id', (int) $identifier);
                }

                $query->orWhere('uuid', $identifier);
            })
            ->first();
    }

    private function exercisesMatch(array $existingExercise, array $incomingExercise): bool
    {
        $existingExerciseId = $this->getNumericExerciseId($existingExercise);
        $incomingExerciseId = $this->getNumericExerciseId($incomingExercise);

        if ($existingExerciseId !== null && $incomingExerciseId !== null) {
            return $existingExerciseId === $incomingExerciseId;
        }

        if (isset($existingExercise['id'], $incomingExercise['id'])) {
            return (string) $existingExercise['id'] === (string) $incomingExercise['id'];
        }

        if (isset($existingExercise['name'], $incomingExercise['name'])) {
            return trim((string) $existingExercise['name']) !== ''
                && strcasecmp(trim((string) $existingExercise['name']), trim((string) $incomingExercise['name'])) === 0;
        }

        return false;
    }

    private function getNumericExerciseId(array $exerciseData): ?int
    {
        $candidate = $exerciseData['exercise_id'] ?? $exerciseData['id'] ?? null;

        if ($candidate === null) {
            return null;
        }

        if (is_int($candidate)) {
            return $candidate;
        }

        if (is_string($candidate) && ctype_digit($candidate)) {
            return (int) $candidate;
        }

        return null;
    }

    /**
     * Process exercises that transitioned to completed and trigger XP events.
     *
     * @return array<int, array<string, mixed>>
     */
    private function processCompletedExercises(Student $student, Workout $workout, array $completedExercises): array
    {
        $events = [];
        $sessionInstanceId = $workout->ensureSessionInstanceId();

        // Reduce accidental duplicate toggles in the same payload.
        $uniqueExercises = collect($completedExercises)
            ->filter(fn ($exercise) => is_array($exercise))
            ->unique(function (array $exercise): string {
                $numericId = $this->getNumericExerciseId($exercise);

                if ($numericId !== null) {
                    return 'exercise:' . $numericId;
                }

                return 'raw:' . md5(json_encode($exercise));
            })
            ->values();

        /** @var array<int, mixed> $exercisePayload */
        foreach ($uniqueExercises as $exercisePayload) {
            if (!is_array($exercisePayload)) {
                continue;
            }

            $exerciseId = $this->getNumericExerciseId($exercisePayload);

            if ($exerciseId === null) {
                $events[] = [
                    'awarded' => false,
                    'awarded_xp' => 0,
                    'xp' => 0,
                    'xp_gained' => 0,
                    'reason' => 'exercise_identifier_missing',
                ];
                continue;
            }

            $exercise = Exercise::find($exerciseId);

            if (!$exercise) {
                $events[] = [
                    'exercise_id' => $exerciseId,
                    'awarded' => false,
                    'awarded_xp' => 0,
                    'xp' => 0,
                    'xp_gained' => 0,
                    'reason' => 'exercise_not_found',
                ];
                continue;
            }

            $alreadyAwardedInSession = ExerciseCompletionLog::query()
                ->where('student_id', $student->id)
                ->where('exercise_id', $exercise->id)
                ->where('session_instance_id', $sessionInstanceId)
                ->exists();

            if ($alreadyAwardedInSession) {
                $profileCurrent = $this->formatGamificationProfile($student);

                $events[] = [
                    'exercise_id' => $exercise->id,
                    'exercise_name' => $exercise->name,
                    'awarded' => false,
                    'awarded_xp' => 0,
                    'xp' => 0,
                    'xp_gained' => 0,
                    'reason' => 'already_awarded_in_session',
                    'session_instance_id' => $sessionInstanceId,
                    'current_xp' => (int) ($profileCurrent['xp_inside_level'] ?? 0),
                    'total_xp' => (int) ($profileCurrent['total_xp'] ?? 0),
                ];
                continue;
            }

            $profileBefore = $this->formatGamificationProfile($student);

            $awardedLog = $this->gamificationService->processExerciseCompletion(
                student: $student,
                exercise: $exercise,
                workout: $workout
            );

            $student->refresh();
            $profileAfter = $this->formatGamificationProfile($student);

            $awardedXp = (int) ($awardedLog?->xp_earned ?? 0);
            $wasAwarded = $awardedXp > 0;

            $events[] = [
                'exercise_id' => $exercise->id,
                'exercise_name' => $exercise->name,
                'exercise_level' => $exercise->level,
                'awarded' => $wasAwarded,
                'reason' => $wasAwarded ? 'awarded' : 'already_awarded_in_session',
                'awarded_xp' => $awardedXp,
                'xp' => $awardedXp,
                'xp_gained' => $awardedXp,
                'session_instance_id' => $sessionInstanceId,
                'current_xp' => (int) ($profileAfter['xp_inside_level'] ?? 0),
                'total_xp' => (int) ($profileAfter['total_xp'] ?? 0),
                'level_before' => $profileBefore['current_level'],
                'level_after' => $profileAfter['current_level'],
                'tier_before' => $profileBefore['current_tier'],
                'tier_after' => $profileAfter['current_tier'],
                'leveled_up' => $profileAfter['current_level'] > $profileBefore['current_level'],
                'tier_changed' => $profileAfter['current_tier'] !== $profileBefore['current_tier'],
            ];
        }

        return $events;
    }

    private function ensureGamificationProfile(Student $student): void
    {
        $student->gamificationProfile()->firstOrCreate(
            ['student_id' => $student->id],
            [
                'total_xp' => 0,
                'current_level' => 0,
                'current_tier' => 0,
                'active_badge' => 'not_rated',
            ]
        );
    }

    /**
     * @return array<string, int|string|bool|null>
     */
    private function formatGamificationProfile(Student $student): array
    {
        $this->ensureGamificationProfile($student);

        $student->loadMissing('gamificationProfile');
        $profile = $student->gamificationProfile;

        if (!$profile) {
            return [
                'has_profile' => false,
                'total_xp' => 0,
                'current_level' => 0,
                'current_tier' => 0,
                'tier_name' => 'Not Rated',
                'active_badge' => 'not_rated',
                'level_progress_percent' => 0,
                'xp_for_current_level' => 0,
                'xp_for_next_level' => 100,
                'xp_inside_level' => 0,
                'xp_required_inside_level' => 100,
                'total_exercises_completed' => 0,
                'last_exercise_completed_at' => null,
            ];
        }

        $xpForCurrentLevel = StudentGamificationProfile::calculateXpRequiredForLevel((int) $profile->current_level);
        $xpForNextLevel = (int) $profile->xp_for_next_level;

        return [
            'has_profile' => true,
            'total_xp' => (int) $profile->total_xp,
            'current_level' => (int) $profile->current_level,
            'current_tier' => (int) $profile->current_tier,
            'tier_name' => $profile->tier_name,
            'active_badge' => $profile->active_badge,
            'level_progress_percent' => (int) $profile->level_progress_percent,
            'xp_for_current_level' => $xpForCurrentLevel,
            'xp_for_next_level' => $xpForNextLevel,
            'xp_inside_level' => max(0, (int) $profile->total_xp - $xpForCurrentLevel),
            'xp_required_inside_level' => max(0, $xpForNextLevel - $xpForCurrentLevel),
            'total_exercises_completed' => (int) $profile->total_exercises_completed,
            'last_exercise_completed_at' => $profile->last_exercise_completed_at?->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildWorkoutContextPayload(Student $student, ?Workout $workout = null): array
    {
        $student->loadMissing('gamificationProfile');

        $assignment = $workout?->planAssignment;
        if (!$assignment) {
            $assignment = $this->orchestration->resolveActivePlan($student);
        }

        $activeWorkout = $student->workouts()
            ->where('status', WorkoutStatus::IN_PROGRESS)
            ->latest('updated_at')
            ->first();

        return [
            'student' => [
                'id' => $student->id,
                'uuid' => $student->uuid,
                'email' => $student->email,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'full_name' => trim((string) $student->full_name),
                'status' => $student->status,
            ],
            'gamification' => $this->formatGamificationProfile($student),
            'active_plan' => $assignment ? [
                'id' => $assignment->id,
                'uuid' => $assignment->uuid,
                'name' => $assignment->plan?->name ?? $assignment->name,
                'status' => $this->normalizeStatus($assignment->status),
                'starts_at' => $assignment->starts_at?->toIso8601String(),
                'ends_at' => $assignment->ends_at?->toIso8601String(),
                'is_current' => (bool) $assignment->is_current,
            ] : null,
            'active_workout' => $activeWorkout ? [
                'id' => $activeWorkout->id,
                'uuid' => $activeWorkout->uuid,
                'session_instance_id' => $activeWorkout->session_instance_id,
                'status' => $this->normalizeStatus($activeWorkout->status),
                'plan_day' => $activeWorkout->plan_day,
                'cycle_index' => $activeWorkout->cycle_index,
            ] : null,
            'requested_workout_id' => $workout?->id,
            'requested_workout_uuid' => $workout?->uuid,
            'requested_session_instance_id' => $workout?->session_instance_id,
        ];
    }

    private function normalizeStatus(mixed $status): ?string
    {
        if ($status instanceof \BackedEnum) {
            return (string) $status->value;
        }

        if (is_string($status)) {
            return $status;
        }

        return null;
    }

}
