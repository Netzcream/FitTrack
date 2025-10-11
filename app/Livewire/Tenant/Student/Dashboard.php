<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Tenant\Exercise\ExercisePlanAssignment;
use App\Models\Tenant\WorkoutSession;

#[Layout('layouts.student')]
class Dashboard extends Component
{
    public ?ExercisePlanAssignment $assignment = null;
    public ?WorkoutSession $todaySession = null;

    public int $trainingsThisMonth = 0;
    public int $goalThisMonth = 12;
    public string $currentRoutine = 'Sin plan activo';
    public bool $hasPendingPayment = false;

    public function mount()
    {
        $student = Auth::user()?->student;
        if (!$student) {
            abort(403, 'Acceso no autorizado');
        }

        // 🔹 Plan activo actual
        $this->assignment = ExercisePlanAssignment::query()
            ->with('plan')
            ->where('student_id', $student->id)
            ->where('is_active', true)
            ->where('status', 'active')
            ->latest('start_date')
            ->first();

        $this->currentRoutine = $this->assignment?->plan?->name ?? 'Sin plan activo';

        // 🔹 Sesión pendiente o en progreso
        $this->todaySession = WorkoutSession::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->latest('scheduled_date')
            ->first();

        // 🔹 Entrenamientos completados este mes
        $this->trainingsThisMonth = WorkoutSession::query()
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereYear('updated_at', Carbon::now()->year)
            ->count();

        // 🔹 Ejemplo: lógica simple para pago pendiente
        $this->hasPendingPayment = $student->account_status === 'pending_payment';
    }

    public function startOrContinueWorkout()
    {
        if ($this->todaySession) {
            return redirect()->route('tenant.student.workout-today');
        }

        $next = WorkoutSession::ensureNextForStudent(Auth::user()->student->id);

        if ($next) {
            return redirect()->route('tenant.student.workout-today');
        }

        session()->flash('warning', 'No hay entrenamientos disponibles por ahora.');
        return redirect()->route('tenant.student.dashboard');
    }

    public function render()
    {
        return view('livewire.tenant.student.dashboard');
    }
}
