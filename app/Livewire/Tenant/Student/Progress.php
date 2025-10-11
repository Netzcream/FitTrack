<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Tenant\WorkoutSession;
use App\Models\Tenant\Student;

#[Layout('layouts.student')]
class Progress extends Component
{
    public ?Student $student = null;
    public int $sessionsThisMonth = 0;
    public int $sessionsLastMonth = 0;
    public int $totalSessions = 0;
    public float $adherence = 0.0;
    public ?float $lastWeight = null;
    public ?float $lastBodyFat = null;

    public function mount()
    {
        $this->student = Auth::user()?->student;

        if (!$this->student) {
            abort(403, 'Acceso no autorizado');
        }

        // 🔹 Sesiones completadas este mes y el anterior
        $this->sessionsThisMonth = WorkoutSession::query()
            ->where('student_id', $this->student->id)
            ->where('status', 'completed')
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereYear('updated_at', Carbon::now()->year)
            ->count();

        $this->sessionsLastMonth = WorkoutSession::query()
            ->where('student_id', $this->student->id)
            ->where('status', 'completed')
            ->whereMonth('updated_at', Carbon::now()->subMonth()->month)
            ->whereYear('updated_at', Carbon::now()->subMonth()->year)
            ->count();

        // 🔹 Total histórico
        $this->totalSessions = WorkoutSession::where('student_id', $this->student->id)
            ->where('status', 'completed')
            ->count();

        // 🔹 Adherencia promedio
        $this->adherence = round($this->student->avg_adherence_pct ?? 0, 1);

        // 🔹 Últimas métricas corporales
        $this->lastWeight = $this->student->last_weight_kg;
        $this->lastBodyFat = $this->student->last_body_fat_pct;
    }

    public function render()
    {
        return view('livewire.tenant.student.progress');
    }
}
