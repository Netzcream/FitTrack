<?php

namespace App\Livewire\Tenant\Exercises\Plans\Templates;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\ExercisePlanTemplate;
use App\Models\Tenant\Exercise\ExercisePlanTemplateWorkout as TplWorkout;
use App\Models\Tenant\Exercise\ExercisePlanTemplateBlock as TplBlock;
use App\Models\Tenant\Exercise\ExercisePlanTemplateItem as TplItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public string $status = '';
    public int $perPage = 10;

    protected function query()
    {
        return ExercisePlanTemplate::query()
            ->when($this->q, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('name', 'like', "%{$this->q}%")
                       ->orWhere('code', 'like', "%{$this->q}%")
                       ->orWhere('description', 'like', "%{$this->q}%");
                });
            })
            ->when($this->status !== '', fn($q) => $q->where('status', $this->status))
            ->orderByDesc('updated_at')
            ->orderBy('name');
    }

    public function updatingQ() { $this->resetPage(); }
    public function updatingStatus() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function delete(int $id): void
    {
        ExercisePlanTemplate::whereKey($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Plantilla eliminada.');
    }

    /** Duplica TODO: template + workouts + blocks + items */
    public function duplicate(int $id): void
    {
        $src = ExercisePlanTemplate::with([
            'workouts' => fn ($q) => $q->orderBy('week_index')->orderBy('day_index')->orderBy('order'),
            'workouts.blocks' => fn ($q) => $q->orderBy('order'),
            'workouts.blocks.items' => fn ($q) => $q->orderBy('order'),
        ])->findOrFail($id);

        $copy = DB::transaction(function () use ($src) {
            // 1) Header
            $tpl = $src->replicate(['uuid', 'code', 'created_at', 'updated_at']);
            // uuid si existe
            if ($tpl->getAttribute('uuid') !== null) {
                $tpl->uuid = (string) Str::orderedUuid();
            }
            // code único
            $baseCode = $src->code . '-copy';
            $code = $baseCode;
            $suffix = 1;
            while (ExercisePlanTemplate::where('code', $code)->exists()) {
                $code = $baseCode . '-' . $suffix++;
            }
            $tpl->code = $code;

            $tpl->status  = 'draft';
            $tpl->version = 1;
            $tpl->name    = $src->name . ' (Copia)';
            $tpl->push();

            // 2) Workouts → Blocks → Items
            foreach ($src->workouts as $w) {
                /** @var TplWorkout $newW */
                $newW = $w->replicate(['id', 'created_at', 'updated_at']);
                $newW->template_id = $tpl->id;

                // si los workouts tienen uuid
                if ($newW->getAttribute('uuid') !== null) {
                    $newW->uuid = (string) Str::orderedUuid();
                }

                $newW->push();

                foreach ($w->blocks as $b) {
                    /** @var TplBlock $newB */
                    $newB = $b->replicate(['id', 'created_at', 'updated_at']);
                    $newB->workout_id = $newW->id;

                    if ($newB->getAttribute('uuid') !== null) {
                        $newB->uuid = (string) Str::orderedUuid();
                    }

                    $newB->push();

                    foreach ($b->items as $it) {
                        /** @var TplItem $newI */
                        $newI = $it->replicate(['id', 'created_at', 'updated_at']);
                        $newI->block_id = $newB->id;

                        if ($newI->getAttribute('uuid') !== null) {
                            $newI->uuid = (string) Str::orderedUuid();
                        }

                        // prescription (JSON cast) se copia con replicate()
                        $newI->push();
                    }
                }
            }

            return $tpl;
        });

        $this->dispatch('toast', type: 'success', message: 'Plantilla duplicada con contenidos.');
        // Si preferís ir directo al builder:
        $this->redirectRoute(
            'tenant.dashboard.exercises.plans.templates.builder',
            ['template' => $copy->id],
            navigate: true
        );
    }

    public function publish(int $id): void
    {
        $tpl = ExercisePlanTemplate::findOrFail($id);
        $tpl->status = 'published';
        $tpl->version = max(1, (int)$tpl->version);
        $tpl->save();

        $this->dispatch('toast', type: 'success', message: "Publicada v{$tpl->version}.");
    }

    public function render()
    {
        $templates = $this->query()->paginate($this->perPage);
        return view('livewire.tenant.exercises.plans.templates.index', compact('templates'));
    }
}
