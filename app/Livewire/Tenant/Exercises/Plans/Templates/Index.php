<?php

namespace App\Livewire\Tenant\Exercises\Plans\Templates;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Tenant\Exercise\ExercisePlanTemplate;
use App\Models\Tenant\Exercise\ExercisePlanTemplateWorkout as TplWorkout;
use App\Models\Tenant\Exercise\ExercisePlanTemplateBlock as TplBlock;
use App\Models\Tenant\Exercise\ExercisePlanTemplateItem as TplItem;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $q = '';
    /** draft|published|archived|trashed|'' */
    public string $status = '';
    public int $perPage = 10;
    public string $sortField = 'name';
    public string $sortDir = 'asc'; // asc|desc

    protected $queryString = [
        'q'      => ['except' => ''],
        'status' => ['except' => ''],
        'perPage' => ['except' => 10],
        'page'   => ['except' => 1],
        'sortField' => ['except' => 'name'],
        'sortDir'  => ['except' => 'asc'],
    ];

    protected function baseQuery(): Builder
    {
        $q = ExercisePlanTemplate::query()
            ->when($this->q, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('name', 'like', "%{$this->q}%")
                        ->orWhere('code', 'like', "%{$this->q}%")
                        ->orWhere('description', 'like', "%{$this->q}%");
                });
            });

        if ($this->status === 'trashed') {
            $q->onlyTrashed();
        } elseif ($this->status !== '') {
            $q->where('status', $this->status);
        }

        // NEW: whitelist de columnas ordenables
        $allowed = ['name', 'code', 'status', 'version'];
        $field = in_array($this->sortField, $allowed, true) ? $this->sortField : 'name';
        $dir   = $this->sortDir === 'desc' ? 'desc' : 'asc';

        // orden estable secundario por 'id' para evitar saltos
        return $q->orderBy($field, $dir)->orderBy('id', 'asc');
    }
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }
        $this->resetPage();
    }
    public function updatedPerPage($v)
    {
        $this->perPage = (int) $v;
        $this->resetPage();
    }
    public function updatingQ()
    {
        $this->resetPage();
    }
    public function updatingStatus()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $tpl = ExercisePlanTemplate::findOrFail($id);

        // Solo eliminar si está archivada; si no, avisamos
        if ($tpl->status !== 'archived') {
            $this->dispatch('toast', type: 'warning', message: 'Primero archivá la plantilla. Solo se eliminan archivadas.');
            return;
        }

        $tpl->delete();

        $page = $this->page ?? 1;
        if ($page > 1 && $this->baseQuery()->paginate($this->perPage, page: $page)->isEmpty()) {
            $this->setPage($page - 1);
        }

        $this->dispatch('toast', type: 'success', message: 'Plantilla enviada a la papelera.');
    }

    public function forceDelete(int $id): void
    {
        ExercisePlanTemplate::onlyTrashed()->whereKey($id)->forceDelete();
        $this->dispatch('toast', type: 'success', message: 'Plantilla eliminada definitivamente.');
    }


    /** Restaurar desde papelera */
    public function restore(int $id): void
    {
        ExercisePlanTemplate::onlyTrashed()->whereKey($id)->restore();
        $this->dispatch('toast', type: 'success', message: 'Plantilla restaurada.');
    }

    /** Archivar / Desarchivar */
    public function archive(int $id): void
    {
        $tpl = ExercisePlanTemplate::findOrFail($id);
        $tpl->status = 'archived';
        $tpl->save();
        $this->dispatch('toast', type: 'success', message: 'Plantilla archivada.');
    }

    public function unarchive(int $id): void
    {
        $tpl = ExercisePlanTemplate::findOrFail($id);
        $tpl->status = 'draft';
        $tpl->save();
        $this->dispatch('toast', type: 'success', message: 'Plantilla desarchivada.');
    }

    /** Duplica TODO: template + workouts + blocks + items */
    public function duplicate(int $id)
    {
        $src = ExercisePlanTemplate::with([
            'workouts' => fn($q) => $q->orderBy('week_index')->orderBy('day_index')->orderBy('order'),
            'workouts.blocks' => fn($q) => $q->orderBy('order'),
            'workouts.blocks.items' => fn($q) => $q->orderBy('order'),
        ])->findOrFail($id);

        $copy = DB::transaction(function () use ($src) {
            /** @var ExercisePlanTemplate $tpl */
            $tpl = $src->replicate(['uuid', 'code', 'created_at', 'updated_at', 'deleted_at']);
            if ($tpl->getAttribute('uuid') !== null) {
                $tpl->uuid = (string) Str::orderedUuid();
            }

            $baseCode = $src->code . '-copy';
            $code = $baseCode;
            $suffix = 1;
            while (ExercisePlanTemplate::withTrashed()->where('code', $code)->exists()) {
                $code = $baseCode . '-' . $suffix++;
            }
            $tpl->code = $code;

            $tpl->status  = 'draft';
            $tpl->version = 1;
            $tpl->name    = trim(($src->name ?: 'Template') . ' (Copia)');
            $tpl->save();

            foreach ($src->workouts as $w) {
                /** @var TplWorkout $newW */
                $newW = $w->replicate(['id', 'created_at', 'updated_at', 'deleted_at']);
                $newW->template_id = $tpl->id;
                if ($newW->getAttribute('uuid') !== null) {
                    $newW->uuid = (string) Str::orderedUuid();
                }
                $newW->save();

                foreach ($w->blocks as $b) {
                    /** @var TplBlock $newB */
                    $newB = $b->replicate(['id', 'created_at', 'updated_at', 'deleted_at']);
                    $newB->workout_id = $newW->id;
                    if ($newB->getAttribute('uuid') !== null) {
                        $newB->uuid = (string) Str::orderedUuid();
                    }
                    $newB->save();

                    foreach ($b->items as $it) {
                        /** @var TplItem $newI */
                        $newI = $it->replicate(['id', 'created_at', 'updated_at', 'deleted_at']);
                        $newI->block_id = $newB->id;
                        if ($newI->getAttribute('uuid') !== null) {
                            $newI->uuid = (string) Str::orderedUuid();
                        }
                        $newI->save();
                    }
                }
            }

            return $tpl;
        });

        $this->dispatch('toast', type: 'success', message: 'Plantilla duplicada con contenidos.');

        // ir directo a editar el nuevo borrador
        return $this->redirectRoute(
            'tenant.dashboard.exercises.plans.templates.edit',
            ['template' => $copy->id],
            navigate: true
        );
    }

    /** Publicar (regla: published = solo lectura; se edita duplicando) */
    public function publish(int $id): void
    {
        $tpl = ExercisePlanTemplate::findOrFail($id);

        if ($tpl->status === 'published') {
            $tpl->version = (int) $tpl->version + 1;
        } else {
            $tpl->version = max(1, (int) ($tpl->version ?: 1));
        }

        $tpl->status = 'published';
        $tpl->save();

        $this->dispatch('toast', type: 'success', message: "Publicada v{$tpl->version}.");
    }

    public function render()
    {
        $templates = $this->baseQuery()->paginate($this->perPage);

        return view('livewire.tenant.exercises.plans.templates.index', compact('templates'));
    }
}
