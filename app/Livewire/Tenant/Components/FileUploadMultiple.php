<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class FileUploadMultiple extends Component
{
    use WithFileUploads;

    public array $files = [];

    public string $name = 'files';
    public string $accept = '*/*';
    public int $maxFiles = 5;
    public ?string $label = null;
    public string $inputId;
    public array $uploadedUrls = [];
    public ?string $model_id = null;
    public ?string $model_type = null;
    public string $model_id_name = 'id';

    public function mount()
    {
        $this->inputId = 'input_' . $this->name . '_' . uniqid();
        $this->refreshUploaded();
    }

    #[On('refreshUploaded')]
    public function refreshUploaded(): void
    {
        if ($this->model_id && $this->model_type && class_exists($this->model_type)) {
            $model = $this->model_type::where($this->model_id_name, $this->model_id)->first();
            if ($model) {

                $this->uploadedUrls = $model
                    ->getMedia($this->name)
                    ->map(fn($media) => $media->getUrl())
                    ->toArray();
            }
        }
    }

    public function updatedFiles()
    {
        $this->dispatch('filesUploaded', name: $this->name, files: $this->files);
    }

    public function removePreview(int $index): void
    {
        unset($this->files[$index]);
        $this->files = array_values($this->files);
    }

    public function removeFile(int $index): void
    {
        if (!$this->model_id || !$this->model_type) return;

        $model = $this->model_type::where($this->model_id_name, $this->model_id)->first();
        if (!$model) return;

        $media = $model->getMedia($this->name)[$index] ?? null;
        $media?->delete();

        $this->refreshUploaded();
    }

    #[On('saveFiles')]
    public function handleGuardarArchivos(array $payload): void
    {


        $model_type = $this->model_type;
        $model_id = $this->model_id;
        $model_id_name = $this->model_id_name;
        $collection = $payload['collection'] ?? 'default';
        if (!is_subclass_of($model_type, Model::class)) return;
        $model = $model_type::where($model_id_name, $model_id)->first();
        if (!$model) return;
        foreach ($this->files as $file) {
            $model->addMedia($file)->toMediaCollection($collection);
        }
        $this->reset('files');
        $this->dispatch('multi-files-saved');
        $this->refreshUploaded();
    }

    public function render()
    {
        return view('livewire.tenant.components.file-upload-multiple');
    }
}
