<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\{Student, Workout, StudentPlanAssignment, Invoice};
use App\Services\{WorkoutOrchestrationService};
use App\Services\Tenant\InvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Layout('layouts.student')]
class Dashboard extends Component
{
    public ?Student $student = null;
    public ?StudentPlanAssignment $assignment = null;
    public ?Workout $todayWorkout = null;
    public ?Workout $activeWorkout = null;
    public array $progressData = [];
    public int $trainingsThisMonth = 0;
    public int $goalThisMonth = 12;
    public bool $hasPendingPayment = false;
    public ?string $noActivePlanMessage = null;
    public array $planHistory = [];

    private WorkoutOrchestrationService $orchestration;

    public function mount(): void
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

        // Resolver plan activo con el servicio
        $this->assignment = $this->orchestration->resolveActivePlan($this->student);

        if (!$this->assignment) {
            $this->noActivePlanMessage = 'No tenés un plan activo. Contactá a tu entrenador.';
            $this->trainingsThisMonth = 0;
            $this->goalThisMonth = 12;
            $this->progressData = [];
            return;
        }

        // Obtener workout activo (in_progress) o crear uno para hoy
        $this->activeWorkout = $this->assignment->workouts()
            ->where('student_id', $this->student->id)
            ->where('status', 'in_progress')
            ->first();

        // Si no hay activo, obtener o crear para hoy
        if (!$this->activeWorkout) {
            $this->todayWorkout = $this->orchestration->getOrCreateTodayWorkout($this->student, $this->assignment);
        } else {
            $this->todayWorkout = $this->activeWorkout;
        }

        // Calcular progreso
        $this->progressData = $this->orchestration->calculateProgress($this->assignment);

        // Entrenamientos completados este mes
        $this->trainingsThisMonth = $this->resolveTrainingsThisMonth();

        // Meta mensual
        $this->goalThisMonth = data_get($this->student->data, 'training.monthly_goal', 12);

        // Verificar si hay invoices pendientes (nuevo sistema)
        if (class_exists(Invoice::class)) {
            $invoiceService = new InvoiceService();
            $this->hasPendingPayment = !empty($invoiceService->getNextPendingForStudent($this->student));
        }

        // Cargar historial de planes
        $this->planHistory = $this->student->planAssignments()
            ->with('plan')
            ->orderBy('starts_at', 'desc')
            ->get()
            ->map(function ($assignment) {
                return [
                    'uuid' => $assignment->uuid,
                    'plan_name' => $assignment->plan?->name ?? $assignment->name,
                    'starts_at' => $assignment->starts_at,
                    'ends_at' => $assignment->ends_at,
                    'status' => $assignment->status,
                    'is_current' => $assignment->is_current,
                    'exercises_count' => $assignment->exercises_by_day->flatten(1)->count(),
                    'days_count' => $assignment->exercises_by_day->count(),
                ];
            })->toArray();
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

    private function resolveTrainingsThisMonth(): int
    {
        if (!Schema::hasTable('workouts')) {
            return 0;
        }

        return (int) DB::table('workouts')
            ->where('student_id', $this->student->id)
            ->where('status', 'completed')
            ->whereYear('completed_at', now()->year)
            ->whereMonth('completed_at', now()->month)
            ->count();
    }

    /* ==================== Acciones ==================== */

    public function startOrContinueWorkout()
    {
        if (!$this->todayWorkout) {
            session()->flash('error', 'No se pudo crear el entrenamiento');
            return;
        }

        if ($this->todayWorkout->is_in_progress) {
            session()->flash('info', 'Continuando entrenamiento de hoy');
        } else {
            // Iniciar el workout
            $this->todayWorkout->startWorkout();
            session()->flash('success', 'Entrenamiento iniciado');
        }

        return redirect()->route('tenant.student.workout-show', ['workout' => $this->todayWorkout]);
    }

    public function render()
    {
        return view('livewire.tenant.student.dashboard', [
            'student' => $this->student,
            'assignment' => $this->assignment,
            'todayWorkout' => $this->todayWorkout,
            'activeWorkout' => $this->activeWorkout,
            'progressData' => $this->progressData,
            'trainingsThisMonth' => $this->trainingsThisMonth,
            'goalThisMonth' => $this->goalThisMonth,
            'hasPendingPayment' => $this->hasPendingPayment,
            'noActivePlanMessage' => $this->noActivePlanMessage,
            'planHistory' => $this->planHistory,
        ]);
    }
}
