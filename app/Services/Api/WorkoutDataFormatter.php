<?php

namespace App\Services\Api;

use App\Models\Tenant as CentralTenant;
use App\Models\Tenant\Workout;
use BackedEnum;
use Illuminate\Support\Facades\URL;

class WorkoutDataFormatter
{
    /**
     * Formatea un workout bajo un contrato uniforme para toda la API.
     */
    public function format(Workout $workout): array
    {
        $workout->loadMissing('planAssignment');

        $rawExercisesData = $workout->exercises_data;
        $pdfUrl = $this->resolveWorkoutPdfUrl($workout);
        $exercisesData = $this->attachPdfUrlToExercises($rawExercisesData, $pdfUrl);

        return [
            'id' => $workout->id,
            'uuid' => $workout->uuid,
            'assignment_uuid' => $workout->planAssignment?->uuid,
            'date' => $workout->created_at?->toDateString(),
            'plan_day' => $workout->plan_day,
            'cycle_index' => $workout->cycle_index,
            'status' => $this->normalizeStatus($workout->status),
            'started_at' => $workout->started_at?->toIso8601String(),
            'completed_at' => $workout->completed_at?->toIso8601String(),
            'duration_minutes' => $this->resolveDurationMinutes($workout, $rawExercisesData),
            'calories_burned' => $this->resolveCaloriesBurned($rawExercisesData),
            'rating' => $workout->rating,
            'notes' => $workout->notes,
            'is_completed' => $workout->is_completed,
            'is_in_progress' => $workout->is_in_progress,
            'pdf_url' => $pdfUrl,
            'exercises_data' => $exercisesData,
            'exercises' => $this->normalizeExercises($exercisesData),
            'meta' => $workout->meta,
            'created_at' => $workout->created_at?->toIso8601String(),
            'updated_at' => $workout->updated_at?->toIso8601String(),
        ];
    }

    private function normalizeExercises(mixed $exercisesData): array
    {
        if (!is_array($exercisesData) || !array_is_list($exercisesData)) {
            return [];
        }

        return collect($exercisesData)->map(function ($ex) {
            if (!is_array($ex)) {
                return [];
            }

            return [
                'id' => $ex['id'] ?? $ex['exercise_id'] ?? null,
                'exercise_id' => $ex['exercise_id'] ?? $ex['id'] ?? null,
                'name' => $ex['name'] ?? '',
                'description' => $ex['description'] ?? null,
                'category' => $ex['category'] ?? null,
                'level' => $ex['level'] ?? null,
                'equipment' => $ex['equipment'] ?? null,
                'image_url' => $ex['image_url'] ?? null,
                'images' => $ex['images'] ?? [],
                'pdf_url' => $ex['pdf_url'] ?? null,
                'completed' => (bool) ($ex['completed'] ?? false),
                'sets' => $ex['sets'] ?? [],
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
}
