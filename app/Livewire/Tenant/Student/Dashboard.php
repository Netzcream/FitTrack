<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Student;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Layout('layouts.student')]
class Dashboard extends Component
{
    public ?Student $student = null;
    public ?\App\Models\Tenant\StudentPlanAssignment $assignment = null;
    public ?\App\Models\Tenant\StudentPlanAssignment $todaySession = null;
    public $assignmentsHistory = [];

    public int $trainingsThisMonth = 0;
    public int $goalThisMonth = 12;
    public bool $hasPendingPayment = false;

    public function mount(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $this->student = Student::where('email', $user->email)->firstOrFail();

        // Planes asignados (incluye histórico)
        $this->assignmentsHistory = $this->student->planAssignments()
            ->orderByDesc('starts_at')
            ->get();

        // Plan actual preferido: el que esté vigente; si no, el más reciente
        $this->assignment = $this->assignmentsHistory
            ->first(fn ($a) => $a->is_current)
            ?? $this->assignmentsHistory->first();

        // Métricas de entrenamiento (usa datos reales si existe la tabla workouts)
        $this->trainingsThisMonth = $this->resolveTrainingsThisMonth();
        $this->goalThisMonth = data_get($this->student->data, 'training.monthly_goal', 12);

        // Placeholder: simulamos sesión activa
        $this->todaySession = null;

        // Verificar si hay pagos pendientes (cuando exista el modelo Payment)
        if (class_exists(Payment::class)) {
            $this->hasPendingPayment = Payment::where('student_id', $this->student->id)
                ->whereNull('paid_at')
                ->exists();
        }
    }

    private function resolveTrainingsThisMonth(): int
    {
        // Si aún no existe la tabla (p. ej. ambiente de desarrollo), devolvemos 0 estable
        if (!Schema::hasTable('workouts')) {
            return 0;
        }

        // Contar workouts del estudiante en el mes y año actuales
        return (int) DB::table('workouts')
            ->where('student_id', $this->student->id)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->count();
    }

    /* ------------------ Acciones ------------------ */
    public function startOrContinueWorkout()
    {
        if ($this->todaySession) {
            session()->flash('info', 'Continuando entrenamiento de hoy');
        } else {
            session()->flash('success', 'Nuevo entrenamiento iniciado');
        }

        return redirect()->route('tenant.student.workout-today');
    }

    public function render()
    {
        return view('livewire.tenant.student.dashboard', [
            'student' => $this->student,
            'assignment' => $this->assignment,
            'assignmentsHistory' => $this->assignmentsHistory,
            'trainingsThisMonth' => $this->trainingsThisMonth,
            'goalThisMonth' => $this->goalThisMonth,
            'hasPendingPayment' => $this->hasPendingPayment,
            'todaySession' => $this->todaySession,
        ]);
    }
}
