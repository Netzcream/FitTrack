<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Student;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

#[Layout('layouts.student')]
class Dashboard extends Component
{
    public ?Student $student = null;
    public ?\App\Models\Tenant\StudentPlanAssignment $assignment = null;
    public ?\App\Models\Tenant\StudentPlanAssignment $todaySession = null;

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

        // Plan activo (nuevo modelo)
        $this->assignment = $this->student->planAssignments()
            ->where('is_active', true)
            ->orderByDesc('starts_at')
            ->first();

        // Simulación de sesiones (ajustar cuando se implemente el tracking real)
        $this->trainingsThisMonth = rand(0, 15);
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
            'trainingsThisMonth' => $this->trainingsThisMonth,
            'goalThisMonth' => $this->goalThisMonth,
            'hasPendingPayment' => $this->hasPendingPayment,
            'todaySession' => $this->todaySession,
        ]);
    }
}
