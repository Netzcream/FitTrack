<?php

namespace App\Services\Api;

use App\Models\Tenant\Exercise;
use App\Models\Tenant\ExerciseCompletionLog;
use App\Models\Tenant as CentralTenant;
use App\Models\Tenant\Workout;
use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

class WorkoutDataFormatter
{
    /**
     * @var array<int, array<int, array{url: string, thumb: string}>>
     */
    private array $exerciseImagesCache = [];

    /**
     * Formatea un workout bajo un contrato uniforme para toda la API.
     */
    public function format(Workout $workout): array
    {
        $workout->loadMissing('planAssignment', 'student');

        $rawExercisesData = $this->hydrateExerciseImages($workout->exercises_data);
        $pdfUrl = $this->resolveWorkoutPdfUrl($workout);
        $exercisesData = $this->attachPdfUrlToExercises($rawExercisesData, $pdfUrl);
        $sessionXpLogs = $this->loadSessionExerciseXpLogs($workout, $exercisesData);
        $normalizedExercises = $this->normalizeExercises($exercisesData, $sessionXpLogs);
        $exercisesDataWithComputed = $this->attachComputedFieldsToRawExercises($exercisesData, $normalizedExercises);

        $totalExercises = count($normalizedExercises);
        $completedExercises = collect($normalizedExercises)
            ->filter(fn (array $exercise) => (bool) ($exercise['completed'] ?? false))
            ->count();
        $remainingExercises = max(0, $totalExercises - $completedExercises);

        $totalSets = (int) collect($normalizedExercises)->sum(fn (array $exercise) => (int) ($exercise['sets_total'] ?? 0));
        $completedSets = (int) collect($normalizedExercises)->sum(fn (array $exercise) => (int) ($exercise['sets_completed'] ?? 0));
        $remainingSets = max(0, $totalSets - $completedSets);
        $setsCompletionPercentage = $totalSets > 0
            ? round(($completedSets / $totalSets) * 100, 1)
            : 0.0;

        $completionPercentage = $totalExercises > 0
            ? round(($completedExercises / $totalExercises) * 100, 1)
            : 0.0;

        $meta = is_array($workout->meta) ? $workout->meta : [];
        $liveElapsedMinutes = (int) ($meta['live_elapsed_minutes'] ?? 0);
        $liveElapsedSeconds = (int) ($meta['live_elapsed_seconds'] ?? ($liveElapsedMinutes * 60));
        if ($liveElapsedMinutes === 0 && $liveElapsedSeconds > 0) {
            $liveElapsedMinutes = (int) floor($liveElapsedSeconds / 60);
        }
        $liveEffort = isset($meta['live_effort']) ? (int) $meta['live_effort'] : null;
        $liveLastSyncAt = is_string($meta['live_last_sync_at'] ?? null) && trim((string) $meta['live_last_sync_at']) !== ''
            ? (string) $meta['live_last_sync_at']
            : $workout->updated_at?->toIso8601String();
        $normalizedStatus = $this->normalizeStatus($workout->status);

        $xpBaseTotal = (int) collect($normalizedExercises)->sum(fn (array $exercise) => (int) data_get($exercise, 'xp.base_value', 0));
        $xpAwardedInSessionTotal = (int) collect($normalizedExercises)->sum(fn (array $exercise) => (int) data_get($exercise, 'xp.awarded_in_session_value', 0));
        $xpAvailableNowTotal = (int) collect($normalizedExercises)->sum(fn (array $exercise) => (int) data_get($exercise, 'xp.to_award_if_complete_now', 0));
        $xpAwardedInSessionExercises = (int) collect($normalizedExercises)->filter(fn (array $exercise) => (bool) data_get($exercise, 'xp.already_awarded_in_session', false))->count();
        $xpAvailableExercises = (int) collect($normalizedExercises)->filter(fn (array $exercise) => (int) data_get($exercise, 'xp.to_award_if_complete_now', 0) > 0)->count();

        $timerShouldRun = $normalizedStatus === 'in_progress' && !($totalExercises > 0 && $completedExercises === $totalExercises);
        $sessionState = match ($normalizedStatus) {
            'pending' => 'not_started',
            'in_progress' => 'active',
            'completed' => 'completed',
            'skipped' => 'skipped',
            default => 'unknown',
        };
        $exerciseProgress = $this->buildExerciseProgress($normalizedExercises);

        return [
            'id' => $workout->id,
            'uuid' => $workout->uuid,
            'session_instance_id' => $workout->session_instance_id,
            'assignment_uuid' => $workout->planAssignment?->uuid,
            'date' => $workout->created_at?->toDateString(),
            'plan_day' => $workout->plan_day,
            'cycle_index' => $workout->cycle_index,
            'status' => $normalizedStatus,
            'started_at' => $workout->started_at?->toIso8601String(),
            'completed_at' => $workout->completed_at?->toIso8601String(),
            'duration_minutes' => $this->resolveDurationMinutes($workout, $rawExercisesData),
            'calories_burned' => $this->resolveCaloriesBurned($rawExercisesData),
            'rating' => $workout->rating,
            'notes' => $workout->notes,
            'is_completed' => $workout->is_completed,
            'is_in_progress' => $workout->is_in_progress,
            'can_start' => in_array($normalizedStatus, ['pending', 'in_progress'], true),
            'can_complete' => in_array($normalizedStatus, ['pending', 'in_progress'], true),
            'can_skip' => in_array($normalizedStatus, ['pending', 'in_progress'], true),
            'pdf_url' => $pdfUrl,
            'exercises_data' => $exercisesDataWithComputed,
            'exercises' => $normalizedExercises,
            'progress' => [
                'session_state' => $sessionState,
                'total_exercises' => $totalExercises,
                'completed_exercises' => $completedExercises,
                'remaining_exercises' => $remainingExercises,
                'completion_percentage' => $completionPercentage,
                'exercise_completion_percentage' => $completionPercentage,
                'all_exercises_completed' => $totalExercises > 0 && $completedExercises === $totalExercises,
                'total_sets' => $totalSets,
                'completed_sets' => $completedSets,
                'remaining_sets' => $remainingSets,
                'sets_completion_percentage' => $setsCompletionPercentage,
                'set_completion_percentage' => $setsCompletionPercentage,
                'live_elapsed_minutes' => $liveElapsedMinutes,
                'live_elapsed_seconds' => $liveElapsedSeconds,
                'live_effort' => $liveEffort,
                'timer_should_run' => $timerShouldRun,
            ],
            'exercise_progress' => $exerciseProgress,
            'live' => [
                'session_state' => $sessionState,
                'elapsed_minutes' => $liveElapsedMinutes,
                'elapsed_seconds' => $liveElapsedSeconds,
                'effort' => $liveEffort,
                'effort_scale' => '1-10',
                'started_at' => $workout->started_at?->toIso8601String(),
                'completed_at' => $workout->completed_at?->toIso8601String(),
                'last_sync_at' => $liveLastSyncAt,
                'timer_should_run' => $timerShouldRun,
                'duration_minutes_final' => $workout->duration_minutes,
            ],
            'physical_activity' => [
                'session_state' => $sessionState,
                'timer' => [
                    'elapsed_minutes' => $liveElapsedMinutes,
                    'elapsed_seconds' => $liveElapsedSeconds,
                    'should_run' => $timerShouldRun,
                    'started_at' => $workout->started_at?->toIso8601String(),
                    'completed_at' => $workout->completed_at?->toIso8601String(),
                    'last_sync_at' => $liveLastSyncAt,
                ],
                'effort' => [
                    'value' => $liveEffort,
                    'scale_min' => 1,
                    'scale_max' => 10,
                ],
                'exercise_progress' => [
                    'total' => $totalExercises,
                    'completed' => $completedExercises,
                    'remaining' => $remainingExercises,
                    'completion_percentage' => $completionPercentage,
                    'all_completed' => $totalExercises > 0 && $completedExercises === $totalExercises,
                ],
                'set_progress' => [
                    'total' => $totalSets,
                    'completed' => $completedSets,
                    'remaining' => $remainingSets,
                    'completion_percentage' => $setsCompletionPercentage,
                ],
            ],
            'xp_summary' => [
                'base_total_if_all_exercises_rewarded' => $xpBaseTotal,
                'already_awarded_in_session_total' => $xpAwardedInSessionTotal,
                'available_to_earn_now_total' => $xpAvailableNowTotal,
                'exercises_with_xp_already_awarded_in_session' => $xpAwardedInSessionExercises,
                'exercises_with_xp_available_now' => $xpAvailableExercises,
                'anti_farming_rule' => 'same_exercise_once_per_session',
            ],
            'survey' => is_array($meta['survey'] ?? null) ? $meta['survey'] : [],
            'assignment' => $this->formatAssignmentSummary($workout, $pdfUrl),
            'meta' => $meta,
            'created_at' => $workout->created_at?->toIso8601String(),
            'updated_at' => $workout->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Merge computed workout fields into raw exercise payload, preserving unknown keys.
     *
     * @return array<int, array<string, mixed>>|mixed
     */
    private function attachComputedFieldsToRawExercises(mixed $rawExercisesData, array $normalizedExercises): mixed
    {
        if (!is_array($rawExercisesData) || !array_is_list($rawExercisesData)) {
            return $rawExercisesData;
        }

        return collect($rawExercisesData)->map(function ($rawExercise, int $index) use ($normalizedExercises) {
            if (!is_array($rawExercise)) {
                return $rawExercise;
            }

            $computed = $normalizedExercises[$index] ?? null;
            if (!is_array($computed)) {
                return $rawExercise;
            }

            return array_merge($rawExercise, [
                'xp' => $computed['xp'] ?? null,
                'xp_base_value' => $computed['xp_base_value'] ?? null,
                'xp_awarded_in_session' => $computed['xp_awarded_in_session'] ?? null,
                'xp_awarded_in_session_value' => $computed['xp_awarded_in_session_value'] ?? null,
                'xp_to_award_if_complete_now' => $computed['xp_to_award_if_complete_now'] ?? null,
                'progress_state' => $computed['progress_state'] ?? null,
                'sets_total' => $computed['sets_total'] ?? null,
                'sets_completed' => $computed['sets_completed'] ?? null,
                'sets_remaining' => $computed['sets_remaining'] ?? null,
                'sets_completion_percentage' => $computed['sets_completion_percentage'] ?? null,
            ]);
        })->toArray();
    }

    private function normalizeExercises(mixed $exercisesData, Collection $sessionXpLogs): array
    {
        if (!is_array($exercisesData) || !array_is_list($exercisesData)) {
            return [];
        }

        return collect($exercisesData)->map(function ($ex) use ($sessionXpLogs) {
            return $this->normalizeExercise($ex, $sessionXpLogs);
        })->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeExercise(mixed $ex, Collection $sessionXpLogs): array
    {
        if (!is_array($ex)) {
            return [];
        }

        $images = is_array($ex['images'] ?? null) ? array_values($ex['images']) : [];
        $imageUrl = $ex['image_url'] ?? data_get($images, '0.url');
        if (!is_string($imageUrl) || trim($imageUrl) === '') {
            $imageUrl = null;
        }

        $sets = $this->normalizeSets($ex['sets'] ?? []);
        $setsTotal = count($sets);
        $setsCompleted = (int) collect($sets)->filter(fn (array $set) => (bool) ($set['completed'] ?? false))->count();
        $setsCompletionPercentage = $setsTotal > 0 ? round(($setsCompleted / $setsTotal) * 100, 1) : 0.0;

        $exerciseId = $ex['exercise_id'] ?? $ex['id'] ?? null;
        $exerciseId = is_numeric($exerciseId) ? (int) $exerciseId : null;
        $exerciseLevel = (string) ($ex['level'] ?? 'beginner');
        $xpBase = ExerciseCompletionLog::getXpForExerciseLevel($exerciseLevel);

        $sessionLog = $exerciseId !== null ? $sessionXpLogs->get($exerciseId) : null;
        $alreadyAwardedInSession = $sessionLog !== null;
        $awardedInSessionValue = $alreadyAwardedInSession ? (int) ($sessionLog->xp_earned ?? $xpBase) : 0;
        $isCompleted = (bool) ($ex['completed'] ?? false);
        $xpToAwardIfCompleteNow = (!$alreadyAwardedInSession && !$isCompleted) ? $xpBase : 0;

        $exerciseProgressState = match (true) {
            $isCompleted => 'completed',
            $setsCompleted > 0 => 'in_progress',
            default => 'pending',
        };

        return [
            'id' => $ex['id'] ?? $ex['exercise_id'] ?? null,
            'exercise_id' => $ex['exercise_id'] ?? $ex['id'] ?? null,
            'name' => $ex['name'] ?? '',
            'description' => $ex['description'] ?? null,
            'category' => $ex['category'] ?? null,
            'level' => $ex['level'] ?? null,
            'equipment' => $ex['equipment'] ?? null,
            'image_url' => $imageUrl,
            'images' => $images,
            'pdf_url' => $ex['pdf_url'] ?? null,
            'completed' => $isCompleted,
            'detail' => $ex['detail'] ?? null,
            'notes' => $ex['notes'] ?? null,
            'sets' => $sets,
            'sets_total' => $setsTotal,
            'sets_completed' => $setsCompleted,
            'sets_remaining' => max(0, $setsTotal - $setsCompleted),
            'sets_completion_percentage' => $setsCompletionPercentage,
            'progress_state' => $exerciseProgressState,
            'xp_base_value' => $xpBase,
            'xp_awarded_in_session' => $alreadyAwardedInSession,
            'xp_awarded_in_session_value' => $awardedInSessionValue,
            'xp_to_award_if_complete_now' => $xpToAwardIfCompleteNow,
            'xp' => [
                'base_value' => $xpBase,
                'exercise_level' => $exerciseLevel,
                'already_awarded_in_session' => $alreadyAwardedInSession,
                'awarded_in_session_value' => $awardedInSessionValue,
                'to_award_if_complete_now' => $xpToAwardIfCompleteNow,
                'anti_farming_rule' => 'same_exercise_once_per_session',
            ],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $normalizedExercises
     * @return array<int, array<string, mixed>>
     */
    private function buildExerciseProgress(array $normalizedExercises): array
    {
        return collect($normalizedExercises)
            ->map(function (array $exercise): array {
                return [
                    'id' => $exercise['id'] ?? null,
                    'exercise_id' => $exercise['exercise_id'] ?? null,
                    'name' => $exercise['name'] ?? null,
                    'completed' => (bool) ($exercise['completed'] ?? false),
                    // Legacy aliases preserved for compatibility with existing clients.
                    'series' => (int) ($exercise['sets_total'] ?? 0),
                    'completed_series' => (int) ($exercise['sets_completed'] ?? 0),
                    'sets_total' => (int) ($exercise['sets_total'] ?? 0),
                    'sets_completed' => (int) ($exercise['sets_completed'] ?? 0),
                    'sets_remaining' => (int) ($exercise['sets_remaining'] ?? 0),
                    'sets_completion_percentage' => (float) ($exercise['sets_completion_percentage'] ?? 0),
                    'progress_state' => $exercise['progress_state'] ?? 'pending',
                    'xp_base_value' => (int) ($exercise['xp_base_value'] ?? 0),
                    'xp_to_award_if_complete_now' => (int) ($exercise['xp_to_award_if_complete_now'] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, mixed>
     */
    private function loadSessionExerciseXpLogs(Workout $workout, mixed $exercisesData): Collection
    {
        if (!is_array($exercisesData) || !array_is_list($exercisesData) || !$workout->student_id) {
            return collect();
        }

        $sessionInstanceId = is_string($workout->session_instance_id) ? trim($workout->session_instance_id) : '';
        if ($sessionInstanceId === '') {
            return collect();
        }

        $exerciseIds = collect($exercisesData)
            ->map(function ($exercise) {
                if (!is_array($exercise)) {
                    return null;
                }

                $candidate = $exercise['exercise_id'] ?? $exercise['id'] ?? null;

                return is_numeric($candidate) ? (int) $candidate : null;
            })
            ->filter()
            ->unique()
            ->values();

        if ($exerciseIds->isEmpty()) {
            return collect();
        }

        return ExerciseCompletionLog::query()
            ->where('student_id', $workout->student_id)
            ->where('session_instance_id', $sessionInstanceId)
            ->whereIn('exercise_id', $exerciseIds->all())
            ->get(['exercise_id', 'xp_earned', 'completed_date', 'created_at', 'session_instance_id'])
            ->keyBy('exercise_id');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeSets(mixed $sets): array
    {
        if (!is_array($sets) || !array_is_list($sets)) {
            return [];
        }

        return collect($sets)->map(function ($set) {
            if (!is_array($set)) {
                return [];
            }

            $time = $set['time'] ?? $set['duration_seconds'] ?? null;
            $durationSeconds = $set['duration_seconds'] ?? $set['time'] ?? null;

            return [
                'reps' => isset($set['reps']) ? (int) $set['reps'] : null,
                'weight' => isset($set['weight']) ? (float) $set['weight'] : null,
                'time' => isset($time) ? (int) $time : null,
                'duration_seconds' => isset($durationSeconds) ? (int) $durationSeconds : null,
                'completed' => (bool) ($set['completed'] ?? false),
            ];
        })->toArray();
    }

    /**
     * Agrega la URL del PDF del plan en cada ejercicio del workout.
     */
    private function attachPdfUrlToExercises(mixed $exercisesData, ?string $pdfUrl): mixed
    {
        if ($pdfUrl === null || !is_array($exercisesData) || !array_is_list($exercisesData)) {
            return $exercisesData;
        }

        return collect($exercisesData)
            ->map(function ($exercise) use ($pdfUrl) {
                if (!is_array($exercise)) {
                    return $exercise;
                }

                if (empty($exercise['pdf_url'])) {
                    $exercise['pdf_url'] = $pdfUrl;
                }

                return $exercise;
            })
            ->values()
            ->all();
    }

    /**
     * Ensure each exercise payload returns the full media gallery when exercise_id is available.
     */
    private function hydrateExerciseImages(mixed $exercisesData): mixed
    {
        if (!is_array($exercisesData) || !array_is_list($exercisesData)) {
            return $exercisesData;
        }

        $exerciseIds = collect($exercisesData)
            ->map(function ($exercise): ?int {
                if (!is_array($exercise)) {
                    return null;
                }

                return $this->extractNumericExerciseId($exercise);
            })
            ->filter()
            ->unique()
            ->values();

        if ($exerciseIds->isEmpty()) {
            return $exercisesData;
        }

        $missingExerciseIds = $exerciseIds
            ->filter(fn (int $exerciseId): bool => !array_key_exists($exerciseId, $this->exerciseImagesCache))
            ->values();

        if ($missingExerciseIds->isNotEmpty()) {
            $exerciseMap = Exercise::query()
                ->with('media')
                ->whereIn('id', $missingExerciseIds->all())
                ->get()
                ->keyBy('id');

            foreach ($missingExerciseIds as $exerciseId) {
                /** @var Exercise|null $exercise */
                $exercise = $exerciseMap->get($exerciseId);
                $this->exerciseImagesCache[$exerciseId] = $this->buildExerciseImagesPayload($exercise);
            }
        }

        return collect($exercisesData)
            ->map(function ($exercise) {
                if (!is_array($exercise)) {
                    return $exercise;
                }

                $exerciseId = $this->extractNumericExerciseId($exercise);
                if ($exerciseId === null) {
                    return $exercise;
                }

                $images = $this->exerciseImagesCache[$exerciseId] ?? [];
                if ($images === []) {
                    return $exercise;
                }

                $exercise['images'] = $images;
                $exercise['image_url'] = is_string($exercise['image_url'] ?? null) && trim((string) $exercise['image_url']) !== ''
                    ? (string) $exercise['image_url']
                    : ($images[0]['url'] ?? null);

                return $exercise;
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{url: string, thumb: string}>
     */
    private function buildExerciseImagesPayload(?Exercise $exercise): array
    {
        if (!$exercise) {
            return [];
        }

        return $exercise->getMedia('images')
            ->map(function ($media): array {
                return [
                    'url' => $media->getFullUrl(),
                    'thumb' => $media->getFullUrl('thumb'),
                ];
            })
            ->values()
            ->all();
    }

    private function extractNumericExerciseId(array $exercise): ?int
    {
        $candidate = $exercise['exercise_id'] ?? $exercise['id'] ?? null;

        if (is_int($candidate)) {
            return $candidate;
        }

        if (is_string($candidate) && ctype_digit($candidate)) {
            return (int) $candidate;
        }

        return null;
    }

    /**
     * Construye la URL absoluta para descargar el PDF del plan asociado al workout.
     */
    private function resolveWorkoutPdfUrl(Workout $workout): ?string
    {
        $assignmentUuid = $workout->planAssignment?->uuid;

        if (!is_string($assignmentUuid) || trim($assignmentUuid) === '') {
            return null;
        }

        try {
            $relativePath = URL::signedRoute(
                'tenant.plan-download-public',
                ['assignment' => $assignmentUuid],
                null,
                false
            );
        } catch (\Throwable $exception) {
            return null;
        }

        return $this->tenantUrl($relativePath);
    }

    private function tenantUrl(string $path): string
    {
        if (!str_starts_with($path, '/')) {
            $path = '/' . ltrim($path, '/');
        }

        return rtrim($this->resolveTenantBaseUrl(), '/') . $path;
    }

    private function resolveTenantBaseUrl(): string
    {
        $tenant = tenant();

        if ($tenant instanceof CentralTenant) {
            try {
                $domain = $tenant->domains()->orderBy('id')->value('domain');
                if (is_string($domain) && trim($domain) !== '') {
                    $domain = trim($domain);

                    if (str_starts_with($domain, 'http://') || str_starts_with($domain, 'https://')) {
                        return rtrim($domain, '/');
                    }

                    return $this->resolveScheme() . '://' . ltrim($domain, '/');
                }
            } catch (\Throwable $exception) {
                // fallback a app.url
            }
        }

        $appUrl = trim((string) config('app.url', 'https://fittrack.com.ar'));
        if ($appUrl === '') {
            return 'https://fittrack.com.ar';
        }

        return rtrim($appUrl, '/');
    }

    private function resolveScheme(): string
    {
        try {
            if (app()->bound('request')) {
                $request = request();
                if ($request && method_exists($request, 'getScheme')) {
                    $scheme = $request->getScheme();
                    if (in_array($scheme, ['http', 'https'], true)) {
                        return $scheme;
                    }
                }
            }
        } catch (\Throwable $exception) {
            // fallback below
        }

        $scheme = parse_url((string) config('app.url', ''), PHP_URL_SCHEME);
        if (is_string($scheme) && in_array($scheme, ['http', 'https'], true)) {
            return $scheme;
        }

        return app()->environment('local') ? 'http' : 'https';
    }

    private function normalizeStatus(mixed $status): ?string
    {
        if ($status instanceof BackedEnum) {
            return (string) $status->value;
        }

        if (is_string($status)) {
            return $status;
        }

        return null;
    }

    private function resolveDurationMinutes(Workout $workout, mixed $rawExercisesData): int
    {
        if (is_int($workout->duration_minutes)) {
            return $workout->duration_minutes;
        }

        if (is_array($rawExercisesData) && !array_is_list($rawExercisesData)) {
            $fromPayload = $rawExercisesData['duration_minutes'] ?? 0;
            return (int) $fromPayload;
        }

        return 0;
    }

    private function resolveCaloriesBurned(mixed $rawExercisesData): int
    {
        if (!is_array($rawExercisesData) || array_is_list($rawExercisesData)) {
            return 0;
        }

        return (int) ($rawExercisesData['calories_burned'] ?? 0);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function formatAssignmentSummary(Workout $workout, ?string $pdfUrl): ?array
    {
        $assignment = $workout->planAssignment;
        if (!$assignment) {
            return null;
        }

        $daysCount = $assignment->exercises_by_day->count();
        $exercisesCount = $assignment->exercises_by_day->flatten(1)->count();

        return [
            'id' => $assignment->id,
            'uuid' => $assignment->uuid,
            'name' => $assignment->plan?->name ?? $assignment->name,
            'status' => $this->normalizeStatus($assignment->status),
            'starts_at' => $this->formatDate($assignment->starts_at),
            'ends_at' => $this->formatDate($assignment->ends_at),
            'is_current' => (bool) $assignment->is_current,
            'days_count' => $daysCount,
            'exercises_count' => $exercisesCount,
            'download_pdf_url' => $pdfUrl,
        ];
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->toIso8601String();
        }

        return null;
    }
}
