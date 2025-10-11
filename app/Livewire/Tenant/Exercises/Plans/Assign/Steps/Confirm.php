<?php

namespace App\Livewire\Tenant\Exercises\Plans\Assign\Steps;

use Livewire\Component;
use App\Models\Tenant\Student;
use App\Models\Tenant\Exercise\ExercisePlanTemplate;
use App\Services\Tenant\Exercise\InstantiateExercisePlanTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Confirm extends Component
{
    public array $state = [];

    /** Asignar directamente */
    public function confirm(InstantiateExercisePlanTemplate $service)
    {

        /** @var User $user */
        $user = Auth::user();

        foreach ($this->state['student_ids'] as $studentId) {
            $student  = Student::findOrFail($studentId);
            $template = ExercisePlanTemplate::findOrFail($this->state['template_id']);

            $service->handle([
                'template_id' => $template->id,
                'user_id'     => $user->id,
                'plan_name'   => $this->state['name_override'] ?: $template->name,
                'start_date'  => $this->state['start_date'],
                'student_id'  => $student->id,
            ]);
        }

        $this->dispatch('toast', type: 'success', message: 'Rutina asignada correctamente.');
        return redirect()->route('tenant.dashboard.exercises.plans.assignments.index');
    }

    /** Clonar y abrir para personalizar antes de asignar */
    public function cloneAndEdit(InstantiateExercisePlanTemplate $service)
    {
        $template = ExercisePlanTemplate::findOrFail($this->state['template_id']);
        /** @var User $user */
        $user = Auth::user();
        $result = $service->handle([
            'template_id'        => $template->id,
            'user_id'            => $user->id,
            'plan_name'          => $this->state['name_override'] ?: $template->name,
            'start_date'         => $this->state['start_date'],
            'assign_immediately' => false, // ← flag que luego ignoramos si no existe
        ]);

        $plan = $result['plan'];

        $this->dispatch('toast', type: 'info', message: 'Plan clonado. Personalizá antes de asignar.');

        return redirect()->route('tenant.dashboard.exercises.plans.builder', ['plan' => $plan->id]);


    }

    /** Volver al paso anterior */
    public function back(): void
    {
        $this->dispatch('prev-step', state: $this->state);
    }

    public function render()
    {
        $students = \App\Models\Tenant\Student::whereIn('id', $this->state['student_ids'])->get();
        $template = \App\Models\Tenant\Exercise\ExercisePlanTemplate::find($this->state['template_id']);

        return view('livewire.tenant.exercises.plans.assign.steps.confirm', [
            'students' => $students,
            'template' => $template,
        ]);
    }
}
