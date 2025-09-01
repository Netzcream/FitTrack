<?php

namespace App\Livewire\Central\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

#[Layout('components.layouts.app')]
class LogViewer extends Component
{
    public array $files = [];
    public ?string $selectedFile = null;
    public string $content = '';

    public function mount()
    {
        $this->loadFiles();
    }

    public function selectFile(string $filename)
    {
        if (Str::contains($filename, ['../', '..\\'])) {
            abort(403, 'Nombre inválido');
        }

        $this->selectedFile = $filename;
        $this->loadContent();
    }


    public function clear()
    {
        if (!$this->selectedFile) return;

        $path = storage_path("logs/{$this->selectedFile}");
        abort_unless(File::exists($path), 404);
        File::put($path, '');
        $this->content = '';
        session()->flash('message', 'Log borrado exitosamente.');
    }
    protected function loadFiles()
    {
        $files = collect(File::files(storage_path('logs')))
            ->filter(fn($f) => Str::endsWith($f->getFilename(), '.log'))
            ->sortByDesc(fn($f) => $f->getMTime())
            ->values();

        $this->files = $files->map(fn($f) => $f->getFilename())->toArray();

        $this->selectedFile ??= $this->files[0] ?? null;
        $this->loadContent();
    }

    protected function loadContent()
    {
        if (!$this->selectedFile) return;

        $path = storage_path("logs/{$this->selectedFile}");
        if (File::exists($path)) {
            $this->content = File::get($path);
        } else {
            $this->content = '';
        }
    }

    public function render()
    {
        return view('livewire.central.dashboard.log-viewer');
    }
}
