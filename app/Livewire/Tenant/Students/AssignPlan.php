<?php

namespace App\Livewire\Tenant\Students;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Tenant\Student;
use App\Models\Tenant\TrainingPlan;
use App\Services\Tenant\AssignPlanService;
use Carbon\Carbon;
use App\Livewire\Tenant\Students\Index as StudentsIndex;

class AssignPlan extends Component
{
    public ?Student $student = null;
    public ?int $training_plan_id = null;
    public ?string $starts_at = null;
    public ?string $ends_at = null;
    public bool $startNow = false;

    public array $plans = [];
    public ?string $currentPlanInfo = null;
    public string $search = '';
    public ?array $selectedPlan = null;
    public ?object $currentPlan = null;
    public ?string $futurePlanInfo = null;
    public bool $hasFuturePlan = false;

    protected array $validationAttributes = [
        'training_plan_id' => 'Plan de entrenamiento',
    ];

    public function mount(Student $student): void
    {
        $this->student = $student;
        $this->loadPlans();
        $this->loadCurrentPlanInfo();
        $this->loadFuturePlanInfo();

        // Calcular fechas por defecto según plan actual
        $this->currentPlan = $this->student->currentPlanAssignment;

        if ($this->currentPlan && $this->currentPlan->is_active && $this->currentPlan->ends_at && !$this->currentPlan->ends_at->isPast()) {
            // Tiene plan activo vigente: empezar después del vencimiento
            $this->starts_at = $this->currentPlan->ends_at->addDay()->format('Y-m-d');
            $this->ends_at = $this->currentPlan->ends_at->addDay()->addMonth()->format('Y-m-d');
            $this->startNow = false;
        } else {
            // No tiene plan o está vencido: empezar hoy
            $this->starts_at = now()->format('Y-m-d');
            $this->ends_at = now()->addMonth()->format('Y-m-d');
            $this->startNow = true; // Por defecto marcado si no hay plan activo
        }
    }

    public function loadPlans(): void
    {
        $query = TrainingPlan::query()
            ->where('is_active', true)
            ->whereNull('student_id'); // Solo plantillas públicas

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $this->plans = $query
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'created_at'])
            ->map(fn($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'date' => $plan->created_at->format('d/m/Y')
            ])
            ->toArray();
    }

    public function loadCurrentPlanInfo(): void
    {
        $current = $this->student->currentPlanAssignment;
        if ($current) {
            $this->currentPlanInfo = sprintf(
                '%s (v%s) - Desde %s hasta %s',
                $current->name,
                $current->meta['version'] ?? '1.0',
                $current->starts_at?->format('d/m/Y') ?? '—',
                $current->ends_at?->format('d/m/Y') ?? '—'
            );
        }
    }

    public function loadFuturePlanInfo(): void
    {
        // Buscar plan futuro pendiente usando la relación
        $futurePlan = $this->student->pendingPlanAssignment;

        if ($futurePlan) {
            $this->hasFuturePlan = true;
            $this->futurePlanInfo = sprintf(
                '%s (v%s) - Desde %s hasta %s',
                $futurePlan->name,
                $futurePlan->meta['version'] ?? '1.0',
                $futurePlan->starts_at?->format('d/m/Y') ?? '—',
                $futurePlan->ends_at?->format('d/m/Y') ?? '—'
            );
        }
    }

    public function selectPlan(int $planId): void
    {
        $plan = TrainingPlan::find($planId);
        if ($plan) {
            $this->training_plan_id = $plan->id;
            $this->selectedPlan = [
                'id' => $plan->id,
                'name' => $plan->name,
            ];
            $this->search = '';
        }
    }

    public function clearSelection(): void
    {
        $this->training_plan_id = null;
        $this->selectedPlan = null;
        $this->search = '';
    }

    public function updatedSearch(): void
    {
        $this->loadPlans();
    }

    public function updatedStartNow(): void
    {
        if ($this->startNow) {
            // Empezar ya: desde hoy
            $this->starts_at = now()->format('Y-m-d');
            $this->ends_at = now()->addMonth()->format('Y-m-d');
        } else {
            // Encolar: después del plan actual si existe
            if ($this->currentPlan && $this->currentPlan->ends_at && !$this->currentPlan->ends_at->isPast()) {
                $this->starts_at = $this->currentPlan->ends_at->addDay()->format('Y-m-d');
                $this->ends_at = $this->currentPlan->ends_at->addDay()->addMonth()->format('Y-m-d');
            }
        }
    }

    public function assign(): void
    {
        $validated = $this->validate([
            'training_plan_id' => ['required', 'integer', 'exists:training_plans,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $plan = TrainingPlan::findOrFail($validated['training_plan_id']);

        // Contar planes futuros pendientes antes de asignar
        $futurePlansCount = $this->student->planAssignments()
            ->where('status', \App\Enums\PlanAssignmentStatus::PENDING)
            ->where('starts_at', '>', now())
            ->count();

        $service = new AssignPlanService();
        $service->assign(
            $plan,
            $this->student,
            Carbon::parse($validated['starts_at']),
            Carbon::parse($validated['ends_at']),
            $this->startNow
        );

        // Mensaje de éxito con información sobre planes futuros
        $message = sprintf(
            'Plan "%s" asignado a %s correctamente',
            $plan->name,
            $this->student->full_name
        );

        if ($futurePlansCount > 0) {
            $message .= sprintf(
                '. Se %s %d plan%s futuro%s pendiente%s.',
                $futurePlansCount === 1 ? 'dio de baja' : 'dieron de baja',
                $futurePlansCount,
                $futurePlansCount === 1 ? '' : 'es',
                $futurePlansCount === 1 ? '' : 's',
                $futurePlansCount === 1 ? '' : 's'
            );
        }

        session()->flash('success', $message);

        $this->dispatch('plan-assigned')->to(StudentsIndex::class);
        $this->dispatch('plan-assigned'); // Para que lo escuche Form también
        $this->dispatch('modal-close', name: 'assign-plan-drawer');
    }

    public function render()
    {
        return view('livewire.tenant.students.assign-plan');
    }
}
