<?php

/**
 * EJEMPLO DE INTEGRACIÓN: Livewire Component
 *
 * Este es un ejemplo completo de cómo integrar el sistema de gamificación
 * en un componente Livewire que maneja sesiones de entrenamiento.
 *
 * NOTA: Este archivo es solo de ejemplo. NO es funcional por sí solo.
 *       Adaptá este código a tu implementación específica.
 */

namespace App\Livewire\Tenant\Workouts;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Workout;
use App\Models\Tenant\Exercise;
use App\Events\Tenant\ExerciseCompleted;
use App\Services\Tenant\GamificationService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.tenant')]
class WorkoutSessionExample extends Component
{
    // Propiedades del componente
    public Workout $workout;
    public array $completedExercises = [];
    public array $gamificationStats = [];

    // Estado del workout
    public bool $workoutStarted = false;
    public bool $workoutCompleted = false;

    /**
     * Montar el componente
     */
    public function mount(Workout $workout)
    {
        $this->workout = $workout;

        // Cargar ejercicios ya completados (si existen)
        $this->loadCompletedExercises();

        // Cargar stats de gamificación
        $this->loadGamificationStats();
    }

    /**
     * Iniciar sesión de entrenamiento
     */
    public function startWorkout()
    {
        $this->workout->update([
            'status' => \App\Enums\WorkoutStatus::IN_PROGRESS,
            'started_at' => now(),
        ]);

        $this->workoutStarted = true;
        session()->flash('message', 'Entrenamiento iniciado. ¡Vamos!');
    }

    /**
     * Marcar ejercicio como completado
     * AQUÍ SE DISPARA EL EVENTO DE GAMIFICACIÓN
     */
    public function markExerciseCompleted(int $exerciseId, array $exerciseData)
    {
        // Validar que no esté ya completado
        if (in_array($exerciseId, $this->completedExercises)) {
            session()->flash('error', 'Este ejercicio ya está completado.');
            return;
        }

        // Obtener el ejercicio
        $exercise = Exercise::find($exerciseId);
        if (!$exercise) {
            session()->flash('error', 'Ejercicio no encontrado.');
            return;
        }

        // Obtener el alumno
        /** @var User|null $user */
        $user = Auth::user();
        $student = $user?->student;

        // 1. Guardar progreso del ejercicio en el workout
        $this->saveExerciseProgress($exerciseId, $exerciseData);

        // 2. Agregar a lista de completados
        $this->completedExercises[] = $exerciseId;

        // 3. 🎮 DISPARAR EVENTO DE GAMIFICACIÓN
        event(new ExerciseCompleted(
            student: $student,
            exercise: $exercise,
            workout: $this->workout,
            completedAt: now()
        ));

        // 4. Recargar stats de gamificación
        $this->loadGamificationStats();

        // 5. Feedback visual
        $xpEarned = $this->getXpForLevel($exercise->level);

        $this->dispatch('exercise-completed', [
            'exerciseId' => $exerciseId,
            'exerciseName' => $exercise->name,
            'xpEarned' => $xpEarned,
        ]);

        session()->flash('success', "¡{$exercise->name} completado! +{$xpEarned} XP");

        // 6. Verificar si completó todos los ejercicios
        $this->checkWorkoutCompletion();
    }

    /**
     * Completar workout entero
     */
    public function completeWorkout()
    {
        $this->workout->update([
            'status' => \App\Enums\WorkoutStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        $this->workoutCompleted = true;

        session()->flash('message', '¡Entrenamiento completado! 🎉');

        // Mostrar resumen de XP ganado
        $this->loadGamificationStats();
    }

    /**
     * Cargar ejercicios ya completados
     */
    private function loadCompletedExercises()
    {
        // Aquí implementarías la lógica para cargar ejercicios ya completados
        // según tu sistema de tracking de workouts
        $this->completedExercises = [];
    }

    /**
     * Cargar estadísticas de gamificación
     */
    private function loadGamificationStats()
    {
        $service = new GamificationService();
        /** @var User|null $user */
        $user = Auth::user();
        $student = $user?->student;
        $this->gamificationStats = $service->getStudentStats($student);
    }

    /**
     * Guardar progreso del ejercicio en el workout
     */
    private function saveExerciseProgress(int $exerciseId, array $exerciseData)
    {
        // Aquí implementarías tu lógica específica para guardar
        // el progreso (sets, reps, peso, etc.)

        // Ejemplo conceptual:
        $exercisesData = $this->workout->exercises_data ?? [];

        foreach ($exercisesData as &$ex) {
            if ($ex['id'] === $exerciseId) {
                $ex['completed'] = true;
                $ex['completed_at'] = now()->toISOString();
                $ex['data'] = $exerciseData;
                break;
            }
        }

        $this->workout->update(['exercises_data' => $exercisesData]);
    }

    /**
     * Verificar si completó todos los ejercicios del workout
     */
    private function checkWorkoutCompletion()
    {
        $totalExercises = count($this->workout->exercises_data ?? []);
        $completedCount = count($this->completedExercises);

        if ($completedCount === $totalExercises) {
            $this->completeWorkout();
        }
    }

    /**
     * Obtener XP según nivel de ejercicio
     */
    private function getXpForLevel(?string $level): int
    {
        return match($level) {
            'beginner', 'principiante' => 10,
            'intermediate', 'intermedio' => 15,
            'advanced', 'avanzado' => 20,
            default => 10,
        };
    }

    /**
     * Renderizar componente
     */
    public function render()
    {
        return view('livewire.tenant.workouts.workout-session-example', [
            'exercises' => $this->workout->exercises_data ?? [],
        ]);
    }
}
