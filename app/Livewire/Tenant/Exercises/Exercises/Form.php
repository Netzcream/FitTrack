<?php

namespace App\Livewire\Tenant\Exercises\Exercises;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

use App\Models\Tenant\Exercise\Exercise;
use App\Models\Tenant\Exercise\ExerciseLevel;
use App\Models\Tenant\Exercise\MovementPattern;
use App\Models\Tenant\Exercise\ExercisePlane;
use App\Models\Tenant\Exercise\Muscle;
use App\Models\Tenant\Exercise\Equipment;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    use WithFileUploads;

    public ?int $id = null;
    public bool $editMode = false;

    // Básicos
    public string $name = '';
    public string $code = '';
    public string $status = Exercise::STATUS_DRAFT;

    // Relaciones
    public ?int $exercise_level_id = null;
    public ?int $movement_pattern_id = null;
    public ?int $exercise_plane_id = null;

    // Flags
    public bool $unilateral = false;
    public bool $external_load = false;

    // Modalidad + Prescripción
    public string $default_modality = Exercise::MOD_REPS;
    public array $default_prescription = []; // libre: guardamos como array

    // Campos de texto
    public ?string $tempo_notation = null;
    public ?string $range_of_motion_notes = null;
    public ?string $equipment_notes = null;
    public array $setup_steps = [];     // repeater
    public array $execution_cues = [];  // repeater
    public array $common_mistakes = []; // repeater
    public ?string $breathing = null;
    public ?string $safety_notes = null;

    // Pivots
    public array $muscleRows = [];    // [ ['muscle_id'=>?, 'role'=>'primary', 'involvement_pct'=>null], ...]
    public array $equipmentRows = []; // [ ['equipment_id'=>?, 'is_required'=>false], ...]

    // Media
    public array $newImages = []; // Livewire temp files
    public int $maxImages = 10;

    protected array $newImagesBuffer = [];

    // Navegación
    public bool $back = true;

    public function mount(?Exercise $exercise): void
    {
        if ($exercise && $exercise->exists) {
            $this->editMode = true;
            $this->id = (int) $exercise->id;

            $this->name  = (string) $exercise->name;
            $this->code  = (string) $exercise->code;
            $this->status = (string) ($exercise->status ?? Exercise::STATUS_DRAFT);

            $this->exercise_level_id   = $exercise->exercise_level_id;
            $this->movement_pattern_id = $exercise->movement_pattern_id;
            $this->exercise_plane_id   = $exercise->exercise_plane_id;

            $this->unilateral   = (bool) $exercise->unilateral;
            $this->external_load = (bool) $exercise->external_load;

            $this->default_modality    = (string) ($exercise->default_modality ?? Exercise::MOD_REPS);
            $this->default_prescription = is_array($exercise->default_prescription) ? $exercise->default_prescription : [];

            $this->tempo_notation         = $exercise->tempo_notation;
            $this->range_of_motion_notes  = $exercise->range_of_motion_notes;
            $this->equipment_notes        = $exercise->equipment_notes;
            $this->setup_steps            = array_values((array) $exercise->setup_steps);
            $this->execution_cues         = array_values((array) $exercise->execution_cues);
            $this->common_mistakes        = array_values((array) $exercise->common_mistakes);
            $this->breathing              = $exercise->breathing;
            $this->safety_notes           = $exercise->safety_notes;

            // pivots
            $this->muscleRows = $exercise->muscles()
                ->get()
                ->map(fn($m) => [
                    'muscle_id' => $m->id,
                    'role' => (string) $m->pivot->role,
                    'involvement_pct' => $m->pivot->involvement_pct,
                ])->toArray();

            $this->equipmentRows = $exercise->equipment()
                ->get()
                ->map(fn($e) => [
                    'equipment_id' => $e->id,
                    'is_required' => (bool) $e->pivot->is_required,
                ])->toArray();
        } else {
            // valores iniciales
            $this->setup_steps = [''];
            $this->execution_cues = [''];
            $this->common_mistakes = [''];
            $this->muscleRows = [['muscle_id' => null, 'role' => Exercise::MOD_REPS /*temp*/, 'involvement_pct' => null]];
            $this->muscleRows = []; // empezamos vacío
            $this->equipmentRows = [];
        }
    }

    public function rules(): array
    {
        $mods = [
            Exercise::MOD_REPS,
            Exercise::MOD_TIME,
            Exercise::MOD_DISTANCE,
            Exercise::MOD_CALORIES,
            Exercise::MOD_RPE,
            Exercise::MOD_LOAD_ONLY,
            Exercise::MOD_TEMPO_ONLY
        ];

        return [
            'name'  => ['required', 'string', 'max:150'],
            'code'  => [
                'required',
                'string',
                'max:150',
                $this->editMode
                    ? Rule::unique('exercise_exercises', 'code')->ignore($this->id)
                    : Rule::unique('exercise_exercises', 'code'),
            ],
            'status' => ['required', Rule::in([
                Exercise::STATUS_DRAFT,
                Exercise::STATUS_PUBLISHED,
                Exercise::STATUS_ARCHIVED
            ])],
            'exercise_level_id'   => ['nullable', 'integer', 'exists:exercise_levels,id'],
            'movement_pattern_id' => ['nullable', 'integer', 'exists:exercise_movement_patterns,id'],
            'exercise_plane_id'   => ['nullable', 'integer', 'exists:exercise_planes,id'],

            'unilateral'   => ['boolean'],
            'external_load' => ['boolean'],

            'default_modality' => ['required', Rule::in($mods)],
            'default_prescription' => ['array'],

            'tempo_notation' => ['nullable', 'string', 'max:50'],
            'range_of_motion_notes' => ['nullable', 'string'],
            'equipment_notes' => ['nullable', 'string'],
            'setup_steps' => ['array'],
            'setup_steps.*' => ['nullable', 'string'],
            'execution_cues' => ['array'],
            'execution_cues.*' => ['nullable', 'string'],
            'common_mistakes' => ['array'],
            'common_mistakes.*' => ['nullable', 'string'],
            'breathing' => ['nullable', 'string'],
            'safety_notes' => ['nullable', 'string'],

            // pivots
            'muscleRows' => ['array'],
            'muscleRows.*.muscle_id' => ['nullable', 'integer', 'exists:exercise_muscles,id'],
            'muscleRows.*.role' => ['nullable', 'string', 'in:primary,secondary,stabilizer'],
            'muscleRows.*.involvement_pct' => ['nullable', 'integer', 'min:0', 'max:100'],

            'equipmentRows' => ['array'],
            'equipmentRows.*.equipment_id' => ['nullable', 'integer', 'exists:exercise_equipment,id'],
            'equipmentRows.*.is_required' => ['boolean'],

            // media
            'newImages' => ['array', 'max:10'],
            'newImages.*' => ['image', 'max:10240'], // 10 MB
        ];
    }

    public function addStep(): void
    {
        $this->setup_steps[] = '';
    }
    public function removeStep(int $i): void
    {
        unset($this->setup_steps[$i]);
        $this->setup_steps = array_values($this->setup_steps);
    }

    public function addCue(): void
    {
        $this->execution_cues[] = '';
    }
    public function removeCue(int $i): void
    {
        unset($this->execution_cues[$i]);
        $this->execution_cues = array_values($this->execution_cues);
    }

    public function addMistake(): void
    {
        $this->common_mistakes[] = '';
    }
    public function removeMistake(int $i): void
    {
        unset($this->common_mistakes[$i]);
        $this->common_mistakes = array_values($this->common_mistakes);
    }

    public function addMuscleRow(): void
    {
        $this->muscleRows[] = ['muscle_id' => null, 'role' => 'primary', 'involvement_pct' => null];
    }
    public function removeMuscleRow(int $i): void
    {
        if (isset($this->muscleRows[$i])) {
            unset($this->muscleRows[$i]);
            $this->muscleRows = array_values($this->muscleRows);
        }
    }

    public function addEquipmentRow(): void
    {
        $this->equipmentRows[] = ['equipment_id' => null, 'is_required' => false];
    }
    public function removeEquipmentRow(int $i): void
    {
        if (isset($this->equipmentRows[$i])) {
            unset($this->equipmentRows[$i]);
            $this->equipmentRows = array_values($this->equipmentRows);
        }
    }

    public function generateCodeFromName(): void
    {
        if (trim($this->name) !== '') {
            $this->code = Str::slug($this->name);
        }
    }

    public function removeMedia(int $mediaId): void
    {
        if (!$this->editMode) return;
        $exercise = Exercise::findOrFail($this->id);
        $media = $exercise->media()->where('id', $mediaId)->first();
        if ($media) $media->delete();
        $this->dispatch('$refresh');
    }

    public function save()
    {
        $validated = $this->validate();

        $exercise = $this->editMode
            ? Exercise::findOrFail($this->id)
            : new Exercise();

        // normalizar arrays: quitar vacíos
        $steps = array_values(array_filter($this->setup_steps, fn($v) => trim((string)$v) !== ''));
        $cues  = array_values(array_filter($this->execution_cues, fn($v) => trim((string)$v) !== ''));
        $mist  = array_values(array_filter($this->common_mistakes, fn($v) => trim((string)$v) !== ''));

        $exercise->fill([
            'name'                 => $validated['name'],
            'code'                 => $validated['code'],
            'status'               => $validated['status'],
            'exercise_level_id'    => $validated['exercise_level_id'] ?? null,
            'movement_pattern_id'  => $validated['movement_pattern_id'] ?? null,
            'exercise_plane_id'    => $validated['exercise_plane_id'] ?? null,
            'unilateral'           => (bool) $validated['unilateral'],
            'external_load'        => (bool) $validated['external_load'],
            'default_modality'     => $validated['default_modality'],
            'default_prescription' => $validated['default_prescription'] ?? [],
            'tempo_notation'       => $validated['tempo_notation'] ?? null,
            'range_of_motion_notes' => $validated['range_of_motion_notes'] ?? null,
            'equipment_notes'      => $validated['equipment_notes'] ?? null,
            'setup_steps'          => $steps,
            'execution_cues'       => $cues,
            'common_mistakes'      => $mist,
            'breathing'            => $validated['breathing'] ?? null,
            'safety_notes'         => $validated['safety_notes'] ?? null,
        ])->save();

        // sync músculos (con pivote)
        $syncMuscles = [];
        foreach ($this->muscleRows as $r) {
            $mid = $r['muscle_id'] ?? null;
            if ($mid) {
                $syncMuscles[$mid] = [
                    'role' => $r['role'] ?? 'primary',
                    'involvement_pct' => $r['involvement_pct'] ?? null,
                ];
            }
        }
        $exercise->muscles()->sync($syncMuscles);

        // sync equipamiento (con pivote)
        $syncEquip = [];
        foreach ($this->equipmentRows as $r) {
            $eid = $r['equipment_id'] ?? null;
            if ($eid) {
                $syncEquip[$eid] = ['is_required' => (bool) ($r['is_required'] ?? false)];
            }
        }
        $exercise->equipment()->sync($syncEquip);

        // media: no superar 10 total
        $currentCount = $this->editMode ? $exercise->getMedia('images')->count() : 0;
        $slots = max(0, $this->maxImages - $currentCount);
        $toAdd = array_slice($this->newImages, 0, $slots);

        foreach ($toAdd as $img) {
            $exercise->addMedia($img->getRealPath())
                ->usingFileName($img->getClientOriginalName())
                ->toMediaCollection('images');
        }
        $this->newImages = [];

        if ($this->editMode) {
            $this->dispatch('updated');
            session()->flash('success', __('exercise.exercise_updated'));
            $this->mount($exercise->fresh()->load('media'));
        } else {
            session()->flash('success', __('exercise.exercise_created'));
        }

        if ($this->back) {
            return $this->redirect(route('tenant.dashboard.exercise.exercises.index'), navigate: true);
        }

        return $this->redirect(route('tenant.dashboard.exercise.exercises.edit', $exercise), navigate: true);
    }

    // Hook: antes de que Livewire reemplace newImages con la nueva selección
    public function updatingNewImages($value): void
    {
        // Guardamos lo que ya había acumulado
        $this->newImagesBuffer = $this->newImages ?? [];
    }

    // Hook: después de que Livewire reemplace newImages, mergeamos con el buffer
    public function updatedNewImages($files): void
    {
        // Normalizamos ambos
        $incoming = is_array($files) ? $files : [$files];
        $current  = $this->newImagesBuffer; // lo acumulado hasta antes del cambio

        // Dedupe por filename temporal (propio de Livewire)
        $seen = [];
        $push = function ($file) use (&$seen) {
            $key = method_exists($file, 'getFilename') ? $file->getFilename() : spl_object_hash($file);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                return true;
            }
            return false;
        };

        // Re-agregar lo ya acumulado
        $merged = [];
        foreach ($current as $f) {
            if ($push($f)) $merged[] = $f;
        }
        // Agregar la nueva selección
        foreach ($incoming as $f) {
            if ($push($f)) $merged[] = $f;
        }

        // Respetar tope global: existentes + nuevas ≤ maxImages
        $existingCount = $this->editMode
            ? (\App\Models\Tenant\Exercise\Exercise::find($this->id)?->getMedia('images')->count() ?? 0)
            : 0;

        $slots = max(0, $this->maxImages - $existingCount);
        if (count($merged) > $slots) {
            $merged = array_slice($merged, 0, $slots);
        }

        // Asignamos el acumulado final
        $this->newImages = array_values($merged);

        // Limpiamos el buffer
        $this->newImagesBuffer = [];
    }

    // Quitar una imagen "nueva" antes de guardar
    public function removeNewImage(int $index): void
    {
        if (isset($this->newImages[$index])) {
            unset($this->newImages[$index]);
            $this->newImages = array_values($this->newImages);
        }
    }


    public function render()
    {
        return view('livewire.tenant.exercises.exercises.form', [
            'levels'   => ExerciseLevel::orderBy('name')->get(['id', 'name']),
            'patterns' => MovementPattern::orderBy('name')->get(['id', 'name']),
            'planes'   => ExercisePlane::orderBy('name')->get(['id', 'name']),
            'muscles'  => Muscle::orderBy('name')->get(['id', 'name']),
            'equip'    => Equipment::orderBy('name')->get(['id', 'name']),
            'exercise' => $this->editMode ? Exercise::with('media')->find($this->id) : null,
        ]);
    }
}
