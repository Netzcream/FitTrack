<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Student;
use App\Models\Tenant\Workout;
use App\Models\Tenant\Exercise;
use App\Services\WorkoutOrchestrationService;
use Illuminate\Support\Facades\Auth;
use App\Events\Tenant\ExerciseCompleted;

#[Layout('layouts.student')]
class WorkoutToday extends Component
{
    public ?Student $student = null;
    public ?Workout $workout = null;
    public array $exercisesData = [];
    public int $durationMinutes = 1;
    public ?int $rating = null;
    public ?string $notes = null;
    public array $survey = [];
    public bool $showCompletionForm = false;
    public ?float $currentWeight = null;

    private int $lastPersistedMinute = -1;

    private WorkoutOrchestrationService $orchestration;

    public function mount(?Workout $workout = null): void
    {
        $this->orchestration = new WorkoutOrchestrationService();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $this->student = Student::where('email', $user->email)->firstOrFail();

        // Asegurar perfil de gamificación para mostrar nivel 0 por defecto
        $this->ensureGamificationProfile();

        // Obtener workout por parámetro (route binding por UUID) o el activo del estudiante
        if ($workout) {
            // Verificar que el workout pertenece al estudiante
            if ($workout->student_id !== $this->student->id) {
                abort(403, 'No autorizado para ver este workout');
            }
            $this->workout = $workout;
        } else {
            $this->workout = $this->student->workouts()
                ->where('status', 'in_progress')
                ->first();
        }

        if (!$this->workout) {
            session()->flash('error', 'No hay entrenamiento activo');
            $this->redirect(route('tenant.student.dashboard'));
            return;
        }

        $this->exercisesData = $this->enrichExercises($this->workout->exercises_data ?? []);

        // Inicializar valores en vivo
        $meta = $this->workout->meta ?? [];
        $this->survey = array_merge(['effort' => $meta['live_effort'] ?? 5], $this->survey);
        $this->durationMinutes = $meta['live_elapsed_minutes'] ?? 1;

        // Persist enriched data so subsequent renders have images/metadata
        $this->workout->updateExercisesData($this->exercisesData);
    }

    private function ensureGamificationProfile(): void
    {
        $this->student
            ->gamificationProfile()
            ->firstOrCreate(
                ['student_id' => $this->student->id],
                [
                    'total_xp' => 0,
                    'current_level' => 0,
                    'current_tier' => 0,
                    'active_badge' => 'not_rated',
                ]
            );
    }

    /**
     * Completa datos de ejercicios existentes con info de Exercise (imagen, categoría, etc.)
     */
    private function enrichExercises(array $exercises): array
    {
        return collect($exercises)->map(function ($exercise) {
            if (!isset($exercise['exercise_id'])) {
                return $exercise;
            }

            $full = Exercise::find($exercise['exercise_id']);
            if (!$full) {
                return $exercise;
            }

            $images = $full->getMedia('images')->map(function ($media) {
                return [
                    'url' => $media->getFullUrl(),
                    'thumb' => $media->getFullUrl('thumb'),
                ];
            })->toArray();
            $imageUrl = $images[0]['url'] ?? ($exercise['image_url'] ?? null);

            return array_merge($exercise, [
                'description' => $exercise['description'] ?? $full->description,
                'category' => $exercise['category'] ?? $full->category,
                'level' => $exercise['level'] ?? $full->level,
                'equipment' => $exercise['equipment'] ?? $full->equipment,
                'image_url' => $imageUrl,
                'images' => $images,
            ]);
        })->toArray();
    }

    /**
     * Actualizar datos de un ejercicio
     */
    public function updateExercise(int $index, array $data): void
    {
        if (isset($this->exercisesData[$index])) {
            $this->exercisesData[$index] = array_merge($this->exercisesData[$index], $data);
            // Auto-save
            $this->workout->updateExercisesData($this->exercisesData);
        }
    }

    /**
     * Marcar ejercicio como completado
     */
    public function toggleExerciseComplete(int $index): void
    {
        if (isset($this->exercisesData[$index])) {
            $wasCompleted = $this->exercisesData[$index]['completed'] ?? false;
            $this->exercisesData[$index]['completed'] = !$wasCompleted;
            $this->workout->updateExercisesData($this->exercisesData);

            // Si se marcó como completado (no descompleto), disparar evento de gamificación
            if (!$wasCompleted && ($this->exercisesData[$index]['completed'] ?? false)) {
                // Algunos planes guardan el id como "id" y otros como "exercise_id"; soportamos ambos
                $exerciseId = $this->exercisesData[$index]['exercise_id']
                    ?? $this->exercisesData[$index]['id']
                    ?? null;

                if ($exerciseId) {
                    $exercise = Exercise::find($exerciseId);
                    if ($exercise) {
                        // Verificar si ya fue completado hoy ANTES de disparar evento
                        $alreadyCompleted = \App\Models\Tenant\ExerciseCompletionLog::where('student_id', $this->student->id)
                            ->where('exercise_id', $exerciseId)
                            ->whereDate('completed_date', now()->toDateString())
                            ->exists();

                        // Solo procesar si NO fue completado hoy (anti-farming)
                        if (!$alreadyCompleted) {
                            // Store old level and tier before event
                            $oldLevel = $this->student->gamificationProfile?->current_level ?? 0;
                            $oldTier = $this->student->gamificationProfile?->current_tier ?? 0;

                            event(new ExerciseCompleted($this->student, $exercise, $this->workout));

                            // Recargar el perfil después del evento para obtener XP actualizado
                            $this->student->refresh();
                            $profile = $this->student->gamificationProfile;

                            if ($profile) {
                                $xpGained = \App\Models\Tenant\ExerciseCompletionLog::getXpForExerciseLevel($exercise->level);
                                $this->dispatch('xp-gained', [
                                    'xp' => $xpGained,
                                    'level' => $profile->current_level,
                                    'progress' => $profile->level_progress_percent,
                                    'currentXp' => $profile->total_xp - $profile->xp_for_current_level,
                                    'requiredXp' => $profile->xp_for_next_level - $profile->xp_for_current_level
                                ]);

                                // Check if leveled up
                                if ($profile->current_level > $oldLevel) {
                                    $this->dispatch('level-up', [
                                        'newLevel' => $profile->current_level,
                                        'newProgress' => $profile->level_progress_percent,
                                        'newCurrentXp' => $profile->total_xp - $profile->xp_for_current_level,
                                        'newRequiredXp' => $profile->xp_for_next_level - $profile->xp_for_current_level,
                                        'newTierName' => $profile->tier_name,
                                        'oldTier' => $oldTier,
                                        'newTier' => $profile->current_tier,
                                        'tierChanged' => $profile->current_tier !== $oldTier
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Completar el workout
     */
    public function completeWorkout(): void
    {
        $this->validate([
            'durationMinutes' => 'required|integer|min:1|max:500',
            'rating' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string|max:500',
            'currentWeight' => 'nullable|numeric|min:20|max:300',
        ]);

        $this->workout->completeWorkout(
            $this->durationMinutes,
            $this->rating,
            $this->notes,
            $this->survey
        );

        // Guardar peso si fue ingresado usando el modelo oficial StudentWeightEntry
        if ($this->currentWeight) {
            try {
                \App\Models\Tenant\StudentWeightEntry::create([
                    'student_id' => $this->student->id,
                    'weight_kg' => $this->currentWeight,
                    'source' => 'workout_completion',
                    'recorded_at' => now(),
                    'notes' => 'Registrado al completar entrenamiento',
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error guardando peso: ' . $e->getMessage());
            }
        }

        session()->flash('success', '¡Entrenamiento completado!' . ($this->currentWeight ? ' Peso actualizado.' : ''));
        $this->redirect(route('tenant.student.dashboard'));
    }

    /**
     * Saltar el workout
     */
    public function skipWorkout(?string $reason = null): void
    {
        $this->workout->skip($reason);
        session()->flash('info', 'Entrenamiento saltado');
        $this->redirect(route('tenant.student.dashboard'));
    }

    /** Persist live progress (elapsed minutes and optional effort) */
    public function persistLiveProgress(int $elapsedMinutes, ?int $effort = null): void
    {
        // Debounce by minute to avoid excessive writes
        if ($elapsedMinutes === $this->lastPersistedMinute) {
            return;
        }
        $this->lastPersistedMinute = $elapsedMinutes;
        $meta = $this->workout->meta ?? [];
        $meta['live_elapsed_minutes'] = $elapsedMinutes;
        if ($effort !== null) {
            $meta['live_effort'] = $effort;
        }
        $this->workout->update(['meta' => $meta]);
    }

    /**
     * Obtener progreso actual
     */
    public function getExerciseProgress(): array
    {
        if (!$this->workout) {
            return [];
        }

        return $this->workout->getExerciseProgress();
    }

    public function render()
    {
        return view('livewire.tenant.student.workout-today', [
            'student' => $this->student,
            'workout' => $this->workout,
            'exercisesData' => $this->exercisesData,
            'exerciseProgress' => $this->getExerciseProgress(),
            'durationMinutes' => $this->durationMinutes,
            'rating' => $this->rating,
            'notes' => $this->notes,
            'survey' => $this->survey,
            'showCompletionForm' => $this->showCompletionForm,
            'currentWeight' => $this->currentWeight,
        ]);
    }
}
