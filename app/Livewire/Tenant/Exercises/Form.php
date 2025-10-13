<?php

namespace App\Livewire\Tenant\Exercises;

use Livewire\Component;
use App\Models\Tenant\Exercise;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    use WithFileUploads;

    public ?Exercise $exercise = null;

    public $maxFiles = 16;
    public string $name = '';
    public string $description = '';
    public string $category = '';
    public string $level = '';
    public string $equipment = '';
    public bool $is_active = true;

    public bool $editMode = false;
    public bool $back = false;

    // 🔹 Upload system
    public array $newImages = [];      // buffer del input (wire:model)
    public array $pendingImages = [];  // acumulador real hasta guardar

    public function mount(?Exercise $exercise): void
    {
        if ($exercise && $exercise->exists) {
            $this->exercise = $exercise;
            $this->name = $exercise->name;
            $this->description = $exercise->description ?? '';
            $this->category = $exercise->category ?? '';
            $this->level = $exercise->level ?? '';
            $this->equipment = $exercise->equipment ?? '';
            $this->is_active = $exercise->is_active;
            $this->editMode = true;
        }
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category'    => ['nullable', 'string', 'max:255'],
            'level'       => ['nullable', 'string', 'max:255'],
            'equipment'   => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
            'newImages.*' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function updatedNewImages($files)
    {
        if (!$files) return;

        // 🔹 Obtener cantidad de imágenes guardadas en BD
        $existingCount = $this->exercise?->getMedia('images')->count() ?? 0;

        // 🔹 Unir con las pendientes (acumuladas)
        $merged = array_merge($this->pendingImages, $files);

        // 🔹 Deduplicar (por nombre temporal)
        $unique = collect($merged)
            ->unique(fn($file) => $file->getFilename())
            ->values()
            ->all();

        // 🔹 Limitar según el máximo disponible
        $allowed = max(0, $this->maxFiles - $existingCount);
        $this->pendingImages = array_slice($unique, 0, $allowed);

        // 🔹 Limpiar el input (importante)
        $this->newImages = [];
    }

    public function save()
    {
        $data = $this->validate();

        $exercise = $this->editMode
            ? $this->exercise
            : new Exercise();

        $exercise->fill($data);

        if (!$this->editMode) {
            $exercise->uuid = (string) Str::uuid();
        }

        $exercise->save();

        // 🔹 Guardar imágenes pendientes
        $currentCount = $exercise->getMedia('images')->count();
        $remaining = $this->maxFiles - $currentCount;

        foreach (array_slice($this->pendingImages, 0, $remaining) as $image) {
            $exercise->addMedia($image->getRealPath())
                ->usingFileName(Str::uuid() . '.' . $image->getClientOriginalExtension())
                ->toMediaCollection('images');
        }

        // 🔹 Refrescar el modelo y relación para que el render se actualice
        $exercise->refresh()->load('media');

        // 🔹 Sincronizar propiedad local
        $this->exercise = $exercise;

        // 🔹 Limpiar el buffer después de guardar
        $this->pendingImages = [];
        $this->newImages = [];


        $this->dispatch('saved');

        if ($this->back) {
            return redirect()->route('tenant.dashboard.exercises.index');
        }

        if (!$this->editMode) {
            return redirect()->route('tenant.dashboard.exercises.edit', $exercise->uuid);
        }

        session()->flash(
            'success',
            $this->editMode
                ? __('exercises.updated')
                : __('exercises.created')
        );
    }

    public function deleteImage($mediaId)
    {
        if (!$this->exercise) return;
        $media = $this->exercise->media()->find($mediaId);
        if ($media) $media->delete();
    }

    public function removePending($index)
    {
        unset($this->pendingImages[$index]);
        $this->pendingImages = array_values($this->pendingImages);
    }

    public function render()
    {
        return view('livewire.tenant.exercises.form');
    }
}
