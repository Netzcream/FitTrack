<?php

namespace App\Livewire\Central\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

#[Layout('components.layouts.app', [
    'title' => 'Logs',
])]
class LogViewer extends Component
{
    public array $files = [];
    public ?string $selectedFile = null;
    public string $content = '';
    public string $searchQuery = '';
    public int $refreshInterval = 5; // segundos
    public string $levelFilter = 'all'; // all, error, warning, info, debug
    public bool $autoScroll = true;

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

        File::delete($path);

        // Recargar la lista de archivos
        $files = collect(File::files(storage_path('logs')))
            ->filter(fn($f) => Str::endsWith($f->getFilename(), '.log'))
            ->sortByDesc(fn($f) => $f->getMTime())
            ->values();

        $this->files = $files->map(fn($f) => $f->getFilename())->toArray();

        // Seleccionar el primer archivo disponible
        $this->selectedFile = $this->files[0] ?? null;

        // Cargar el contenido del nuevo archivo
        $this->loadContent();

        session()->flash('message', 'Log eliminado exitosamente.');
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

    public function loadContent()
    {
        if (!$this->selectedFile) return;

        $path = storage_path("logs/{$this->selectedFile}");
        if (File::exists($path)) {
            $content = File::get($path);
            $lines = explode("\n", $content);

            // Aplicar filtros
            $filtered = array_filter($lines, function($line) {
                // Filtro de búsqueda
                if ($this->searchQuery && stripos($line, $this->searchQuery) === false) {
                    return false;
                }

                // Filtro por nivel
                if ($this->levelFilter !== 'all') {
                    $level = strtoupper($this->levelFilter);
                    if (!str_contains($line, $level)) {
                        return false;
                    }
                }

                return true;
            });

            $this->content = implode("\n", $filtered);
        } else {
            $this->content = '';
        }
    }

    public function updateSearch()
    {
        $this->loadContent();
    }

    public function clearSearch()
    {
        $this->searchQuery = '';
        $this->loadContent();
    }

    public function setLevelFilter(string $level)
    {
        $this->levelFilter = $level;
        $this->loadContent();
    }

    public function setRefreshInterval(int $seconds)
    {
        $this->refreshInterval = $seconds;
    }

    public function toggleAutoScroll()
    {
        $this->autoScroll = !$this->autoScroll;
    }

    public function downloadLog()
    {
        if (!$this->selectedFile) return;

        $path = storage_path("logs/{$this->selectedFile}");
        abort_unless(File::exists($path), 404);

        return Response::download($path);
    }

    public function exportCsv()
    {
        if (!$this->selectedFile) return;

        $lines = array_filter(explode("\n", $this->content), fn($l) => trim($l) !== '');
        $filename = str_replace('.log', '', $this->selectedFile) . '.csv';

        return response()->streamDownload(function () use ($lines) {
            $handle = fopen('php://output', 'w');

            // Agregar BOM UTF-8 para Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados
            fputcsv($handle, ['Línea', 'Nivel', 'Contenido']);

            foreach ($lines as $index => $line) {
                $level = 'INFO';
                if (str_contains($line, 'ERROR')) $level = 'ERROR';
                elseif (str_contains($line, 'WARNING') || str_contains($line, 'WARN')) $level = 'WARNING';
                elseif (str_contains($line, 'DEBUG')) $level = 'DEBUG';

                fputcsv($handle, [$index + 1, $level, $line]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        return view('livewire.central.dashboard.log-viewer');
    }

    /**
     * Obtener información de análisis del log
     */
    public function getLogStats()
    {
        $content = $this->content;
        return [
            'total_lines' => substr_count($content, "\n"),
            'errors' => substr_count($content, 'ERROR'),
            'warnings' => substr_count($content, 'WARNING'),
            'info' => substr_count($content, 'INFO'),
        ];
    }
}
