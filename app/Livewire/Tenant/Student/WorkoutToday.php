<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\WorkoutSession;
use App\Models\Tenant\WorkoutSessionSet;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.student')]
class WorkoutToday extends Component
{
    public ?WorkoutSession $session = null;
    public $sets = [];
    public $blocks = [];
    public $showCompletionModal = false;

    public function mount()
    {

        $user = Auth::user();
        $student = $user?->student;

        if (!$student) {
            abort(403, 'No autorizado');
        }

        $this->session = \App\Models\Tenant\WorkoutSession::ensureNextForStudent($student->id);

        if ($this->session) {
            $this->blocks = $this->session->planWorkout
                ->blocks()
                ->with(['items.exercise'])
                ->orderBy('order')
                ->get();
        }
    }


    public function startSession()
    {
        if ($this->session && $this->session->status === 'pending') {
            $this->session->update(['status' => 'in_progress', 'started_at' => now()]);
            $this->session->refresh();
        }
    }

    public function completeSet($setId)
    {
        if ($this->session->status === 'pending') {
            $this->startSession();
        }
        $set = $this->session->sets()->find($setId);
        if ($set && !$set->completed_at) {
            $set->update(['completed_at' => now()]);
        }
        $remaining = $this->session->sets()->whereNull('completed_at')->count();
        if ($remaining === 0) {
            $this->completeSession();
        } else {
            $this->session->refresh();
        }

        $this->session->refresh();
    }

    public function finishDay()
    {
        $this->showCompletionModal = false;
        return redirect()->route('tenant.student.dashboard');
    }

    public function finishSession()
    {
        if ($this->session && $this->session->status !== 'completed') {
            $this->session->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        $this->session->refresh();
        $this->showCompletionModal = true;
        $this->dispatch('sessionCompleted');
    }

    public function completeSession()
    {
        if ($this->session->status !== 'completed') {
            $this->session->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        $this->showCompletionModal = true;
    }

    public function startNextSession()
    {
        $user = Auth::user();
        $studentId = $user?->student->id;
        $next = \App\Models\Tenant\WorkoutSession::ensureNextForStudent($studentId);

        $this->showCompletionModal = false;

        if ($next) {
            return redirect()->route('tenant.student.workout-today');
        }
    }


    public function render()
    {
        return view('livewire.tenant.student.workout-today', [
            'session' => $this->session,
            'sets' => $this->sets,
        ]);
    }
}
