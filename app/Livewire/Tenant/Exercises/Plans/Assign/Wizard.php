<?php

namespace App\Livewire\Tenant\Exercises\Plans\Assign;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use App\Models\Tenant\Student;
use App\Models\Tenant\Exercise\ExercisePlanTemplate;
use App\Models\User;
use App\Services\Tenant\Exercise\InstantiateExercisePlanTemplate;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

#[Layout('components.layouts.tenant')]
class Wizard extends Component
{
    /** Paso actual (1–4) */
    public int $step = 1;

    /** Estado compartido del wizard */
    public array $state = [
        'student_ids'   => [],
        'template_id'   => null,
        'start_date'    => '',
        'name_override' => '',
        'comments'      => '',
    ];

    public function mount(?int $student_id = null): void
    {
        // Si vengo desde la ficha del alumno, precargo y salto al paso 2
        if ($student_id) {
            $this->state['student_ids'] = [$student_id];
            $this->step = 2;
        }
    }

    /** Avanzar al siguiente paso */
    #[On('next-step')]
    public function nextStep(array $state = []): void
    {
        if (!empty($state)) {
            $this->state = $state;
        }
        $this->validateStep();
        $this->step++;
    }
    /** Retroceder al paso anterior */
    #[On('prev-step')]
    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    /** Validaciones específicas de cada paso */
    protected function validateStep(): void
    {
        switch ($this->step) {
            case 1:
                Validator::make($this->state, [
                    'student_ids' => ['required', 'array', 'min:1'],
                ])->validate();
                break;

            case 2:
                Validator::make($this->state, [
                    'template_id' => ['required', 'exists:exercise_plan_templates,id'],
                ])->validate();
                break;

            case 3:
                Validator::make($this->state, [
                    'start_date'    => ['required', 'date'],
                    'name_override' => ['nullable', 'string', 'max:120'],
                ])->validate();
                break;
        }
    }

    /**
     * Confirmar asignación → instanciar plan
     */
    public function confirm(InstantiateExercisePlanTemplate $service): RedirectResponse
    {
        $this->validateStep();

        foreach ($this->state['student_ids'] as $studentId) {
            $student  = Student::findOrFail($studentId);
            $template = ExercisePlanTemplate::findOrFail($this->state['template_id']);

            /** @var User $user */
            $user = Auth::user();
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

    public function render()
    {
        return view('livewire.tenant.exercises.plans.assign.wizard');
    }
}
