<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Student;
use App\Models\Tenant\Workout;
use App\Models\Tenant\Exercise;
use App\Services\WorkoutOrchestrationService;
use Illuminate\Support\Facades\Auth;

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

    private int $lastPersistedMinute = -1;

    private WorkoutOrchestrationService $orchestration;

    public function mount(?int $workoutId = null): void
    {
        $this->orchestration = new WorkoutOrchestrationService();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $this->student = Student::where('email', $user->email)->firstOrFail();

        // Obtener workout por ID o el activo del estudiante
        if ($workoutId) {
            $this->workout = Workout::where('id', $workoutId)
                ->where('student_id', $this->student->id)
                ->firstOrFail();
        } else {
            $this->workout = $this->student->workouts()
                ->where('status', 'in_progress')
                ->first();
        }

        if (!$this->workout) {
            session()->flash('error', 'No hay entrenamiento activo');
            $this->redirect(route('tenant.student.dashboard'));
        }

        $this->exercisesData = $this->enrichExercises($this->workout->exercises_data ?? []);

        // Inicializar valores en vivo
        $meta = $this->workout->meta ?? [];
        $this->survey = array_merge(['effort' => $meta['live_effort'] ?? 5], $this->survey);
        $this->durationMinutes = $meta['live_elapsed_minutes'] ?? 1;

        // Persist enriched data so subsequent renders have images/metadata
        $this->workout->updateExercisesData($this->exercisesData);
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
            $this->exercisesData[$index]['completed'] = !($this->exercisesData[$index]['completed'] ?? false);
            $this->workout->updateExercisesData($this->exercisesData);
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
        ]);

        $this->workout->completeWorkout(
            $this->durationMinutes,
            $this->rating,
            $this->notes,
            $this->survey
        );

        session()->flash('success', '¡Entrenamiento completado! Ahora puedes actualizar tu peso.');
        $this->redirect(route('tenant.student.dashboard'));
    }

    /**
     * Saltar el workout
     */
    public function skipWorkout(string $reason = null): void
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
        ]);
    }
}
