<?php

namespace App\Livewire\Central\Dashboard\Manuals;

use App\Enums\ManualCategory;
use App\Http\Requests\Central\StoreManualRequest;
use App\Http\Requests\Central\UpdateManualRequest;
use App\Models\Central\Manual;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class ManualsForm extends Component
{
    use WithFileUploads;

    public ?Manual $manual = null;

    public string $title = '';
    public string $slug = '';
    public $category = '';
    public string $summary = '';
    public string $content = '';
    public bool $is_active = true;
    public $published_at = null;

    // Archivos
    public $icon;
    public $newAttachments = [];
    public $pendingAttachments = []; // Array acumulativo de archivos pendientes
    public $attachmentsToDelete = [];
    public $attachmentInputKey = 0; // Para resetear los inputs de archivo

    public bool $edit_mode = false;
    public bool $back = false;

    public function mount($manual = null)
    {
        if ($manual) {
            $this->manual = $manual;
            $this->title = $manual->title;
            $this->slug = $manual->slug;
            $this->category = $manual->category->value;
            $this->summary = $manual->summary ?? '';
            $this->content = $manual->content;
            $this->is_active = $manual->is_active;
            $this->published_at = $manual->published_at?->format('Y-m-d');
            $this->edit_mode = true;
        } else {
            // Valores por defecto para nuevo manual
            $this->is_active = true;
            $this->published_at = now()->format('Y-m-d');
        }
    }

    public function rules()
    {
        $uuid = $this->manual?->uuid ?? null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('manuals', 'slug')->ignore($uuid, 'uuid')
            ],
            'category' => ['required', \Illuminate\Validation\Rule::enum(ManualCategory::class)],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'is_active' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'icon' => ['nullable', 'image', 'max:2048'], // 2MB max
            'newAttachments.*' => ['nullable', 'file', 'max:10240'], // 10MB max por archivo
        ];
    }

    public function save()
    {
        // Generar slug automáticamente si está vacío
        if (empty($this->slug)) {
            $this->slug = \Illuminate\Support\Str::slug($this->title);
        }

        $validated = $this->validate();

        $failedFiles = [];

        try {
            if ($this->edit_mode) {
                $this->manual->update($validated);
                $manual = $this->manual;
                $message = __('manuals.updated_success');
            } else {
                $manual = Manual::create($validated);
                $message = __('manuals.created_success');
            }

            // Procesar icono con manejo de errores
            if ($this->icon) {
                try {
                    $manual->clearMediaCollection('icon');
                    $manual->addMedia($this->icon->getRealPath())
                        ->usingFileName($this->icon->getClientOriginalName())
                        ->toMediaCollection('icon');
                } catch (\Throwable $e) {
                    $failedFiles[] = $this->icon->getClientOriginalName();
                    \Log::error('Error al subir icono: ' . $e->getMessage());
                }
            }

            // Procesar archivos adjuntos pendientes con manejo de errores individual
            if (!empty($this->pendingAttachments)) {
                foreach ($this->pendingAttachments as $index => $attachment) {
                    if ($attachment) {
                        try {
                            // Verificar que el archivo temporal existe y es válido
                            if (!file_exists($attachment->getRealPath())) {
                                $failedFiles[] = $attachment->getClientOriginalName();
                                continue;
                            }

                            $manual->addMedia($attachment->getRealPath())
                                ->usingFileName($attachment->getClientOriginalName())
                                ->toMediaCollection('attachments');
                        } catch (\Throwable $e) {
                            $failedFiles[] = $attachment->getClientOriginalName();
                            \Log::error('Error al subir archivo adjunto: ' . $e->getMessage(), [
                                'file' => $attachment->getClientOriginalName(),
                                'index' => $index
                            ]);
                        }
                    }
                }
            }

            // Eliminar archivos marcados
            if (!empty($this->attachmentsToDelete)) {
                foreach ($this->attachmentsToDelete as $mediaId) {
                    try {
                        $media = $manual->media()->find($mediaId);
                        if ($media) {
                            $media->delete();
                        }
                    } catch (\Throwable $e) {
                        \Log::error('Error al eliminar archivo: ' . $e->getMessage(), ['media_id' => $mediaId]);
                    }
                }
            }

            // Limpiar los arrays de archivos temporales después del guardado exitoso
            $this->icon = null;
            $this->pendingAttachments = [];
            $this->attachmentsToDelete = [];
            $this->attachmentInputKey++; // Incrementar para forzar re-render de los inputs

            // Mensaje de éxito con warnings si hubo archivos fallidos
            if (count($failedFiles) > 0) {
                $warningMessage = $message . ' Sin embargo, no se pudieron subir algunos archivos: ' . implode(', ', $failedFiles);
                session()->flash('warning', $warningMessage);
            } else {
                session()->flash('success', $message);
            }

            $this->dispatch('saved');

            if ($this->back) {
                return $this->redirect(route('central.dashboard.manuals.index'), navigate: true);
            }

            // Si es creación y no está marcado "volver", redirigir a edición
            if (!$this->edit_mode) {
                return $this->redirect(route('central.dashboard.manuals.edit', $manual), navigate: true);
            }
        } catch (\Throwable $e) {
            $message = __('Error al guardar: ') . $e->getMessage();
            $this->dispatch('notify', message: $message, type: 'error');
        }
    }

    public function deleteAttachment($mediaId)
    {
        $this->attachmentsToDelete[] = $mediaId;
    }

    public function removeIcon()
    {
        if ($this->manual) {
            $this->manual->clearMediaCollection('icon');
            $this->dispatch('notify', message: __('Icono eliminado'), type: 'success');
        }
        $this->icon = null;
    }

    public function clearIconPreview()
    {
        $this->icon = null;
    }

    // Método que se ejecuta cuando se suben nuevos archivos
    public function updatedNewAttachments()
    {
        // Validar los nuevos archivos
        $this->validate([
            'newAttachments.*' => 'file|max:10240',
        ]);

        // Agregar los nuevos archivos al array de pendientes
        // Solo si son válidos y accesibles
        foreach ($this->newAttachments as $file) {
            try {
                // Verificar que el archivo es válido antes de agregarlo
                if ($file && file_exists($file->getRealPath())) {
                    $this->pendingAttachments[] = $file;
                }
            } catch (\Throwable $e) {
                \Log::warning('Archivo temporal no válido o inaccesible', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Limpiar el input para permitir subir más archivos
        $this->newAttachments = [];
    }

    // Remover archivo pendiente por índice
    public function removePendingAttachment($index)
    {
        if (isset($this->pendingAttachments[$index])) {
            unset($this->pendingAttachments[$index]);
            // Re-indexar el array
            $this->pendingAttachments = array_values($this->pendingAttachments);
        }
    }

    public function getCategories()
    {
        return ManualCategory::options();
    }

    public function render()
    {
        return view('livewire.central.dashboard.manuals.form', [
            'categories' => $this->getCategories(),
        ]);
    }
}
