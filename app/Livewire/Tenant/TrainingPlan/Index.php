<?php

namespace App\Livewire\Tenant\TrainingPlan;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\TrainingPlan;
use Illuminate\Database\Eloquent\Builder;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public ?string $deleteUuid = null;

    /** 🔍 Flag de debug: incluir planes asignados (por defecto false) */
    public bool $includeAssigned = false;



    public ?string $assignUuid = null;
    public string $studentSearch = '';
    public ?string $selectedStudentUuid = null;
    public array $students = [];
    public ?string $assignedFrom = null;
    public ?string $assignedUntil = null;



    /* -------------------- Reactividad -------------------- */
    public function updating($field): void
    {
        if (in_array($field, ['search', 'status', 'includeAssigned'])) {
            $this->resetPage();
        }
    }

    /* -------------------- Ordenamiento -------------------- */
    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    /* -------------------- Filtros -------------------- */
    public function clearFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    /* -------------------- Eliminación -------------------- */
    public function confirmDelete(string $uuid): void
    {
        $this->deleteUuid = $uuid;
    }

    public function delete(): void
    {
        if ($this->deleteUuid) {
            TrainingPlan::where('uuid', $this->deleteUuid)->delete();
            $this->dispatch('plan-deleted');
            $this->deleteUuid = null;
        }
    }

    /* -------------------- Duplicación -------------------- */
    public function clone(string $uuid): void
    {
        $original = TrainingPlan::with(['exercises', 'media'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $clone = $original->duplicate();
        $clone->name = $original->name . ' (Copia)';
        $clone->save();

        $this->dispatch('plan-cloned', name: $clone->name);
    }

    /* -------------------- Preparar modal -------------------- */
    public function prepareAssign(string $uuid): void
    {
        $this->assignUuid = $uuid;
        $this->studentSearch = '';
        $this->selectedStudentUuid = null;
        $this->assignedFrom = null;
        $this->assignedUntil = null;
        $this->students = [];
    }

    /* -------------------- Buscar alumnos dinámicamente -------------------- */
    public function updatedStudentSearch(string $value): void
    {
        if (strlen($value) < 2) {
            $this->students = [];
            return;
        }

        $this->students = \App\Models\Tenant\Student::query()
            ->where(function ($q) use ($value) {
                $t = "%{$value}%";
                $q->where('first_name', 'like', $t)
                    ->orWhere('last_name', 'like', $t)
                    ->orWhere('email', 'like', $t);
            })
            ->orderBy('first_name')
            ->take(10)
            ->get(['uuid', 'first_name', 'last_name', 'email'])
            ->map(fn($s) => [
                'uuid' => $s->uuid,
                'full_name' => trim($s->first_name . ' ' . $s->last_name),
                'email' => $s->email,
            ])
            ->toArray();
    }

    /* -------------------- Asignar plan a alumno -------------------- */
    public function assignToStudent(): void
    {
        if (!$this->assignUuid || !$this->selectedStudentUuid) return;

        $plan = TrainingPlan::with(['exercises', 'media'])
            ->where('uuid', $this->assignUuid)
            ->firstOrFail();

        $student = \App\Models\Tenant\Student::where('uuid', $this->selectedStudentUuid)->firstOrFail();

        // Crear asignación
        $assigned = $plan->assignToStudent($student);

        // Calcular fechas con fallback
        $from = $this->assignedFrom ? \Carbon\Carbon::parse($this->assignedFrom) : now();
        $until = $this->assignedUntil
            ? \Carbon\Carbon::parse($this->assignedUntil)
            : $from->copy()->addMonth();

        $assigned->assigned_from = $from->toDateString();
        $assigned->assigned_until = $until->toDateString();
        $assigned->save();

        $this->dispatch('plan-assigned', name: $assigned->name);
    }


    /* -------------------- Render -------------------- */
    public function render()
    {
        $plans = TrainingPlan::query()
            // Filtro de estado
            ->when(
                $this->status !== '',
                fn(Builder $q) =>
                $q->where('is_active', (bool) $this->status)
            )

            // Filtro de búsqueda
            ->search($this->search)

            // Excluir asignados salvo que $includeAssigned sea true
            ->when(
                !$this->includeAssigned,
                fn(Builder $q) =>
                $q->whereNull('student_id')
            )

            // Orden y paginación
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.tenant.training-plan.index', compact('plans'));
    }
}
