<?php

namespace App\Livewire\Central\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class JobsTable extends Component
{
    use WithPagination;

    // Filtros
    public string $filterStatus = 'pending'; // pending | failed | all
    public string $filterQueue = '';
    public string $sortBy = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    public $queues = [];
    public $stats = [];

    // Modal
    public ?int $deletingJobId = null;
    public ?string $deletingJobType = null; // 'pending' or 'failed'

    // Modal de detalles
    public ?int $selectedJobId = null;
    public $selectedJobDetails = null;
    public string $detailsTab = 'payload'; // tab activo en modal de detalles

    public function mount()
    {
        $this->loadQueues();
        $this->loadStats();
    }

    public function loadQueues()
    {
        $this->queues = DB::table('jobs')
            ->distinct()
            ->pluck('queue')
            ->sort()
            ->values()
            ->toArray();
    }

    public function loadStats()
    {
        $this->stats = [
            'pending' => DB::table('jobs')->count(),
            'failed' => DB::table('failed_jobs')->count(),
        ];
    }

    public function updated($field): void
    {
        // Resetear paginación cuando cambian los filtros
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->filterStatus = 'pending';
        $this->filterQueue = '';
        $this->resetPage();
    }

    public function runNow($jobId)
    {
        $job = DB::table('jobs')->where('id', $jobId)->first();

        if ($job) {
            DB::table('jobs')
                ->where('id', $jobId)
                ->update([
                    'available_at' => now()->timestamp,
                    'reserved_at' => null,
                    'attempts' => 0  // Reset intentos
                ]);

            $this->dispatch('notify', message: 'Job enviado a ejecución inmediata con intentos reseteados', type: 'success');
            // Forzar re-render del componente
            $this->dispatch('table-updated');
        }
    }

    public function confirmDelete($jobId, $jobType = 'pending')
    {
        $this->deletingJobId = $jobId;
        $this->deletingJobType = $jobType;
    }

    public function delete()
    {
        if (!$this->deletingJobId) {
            return;
        }

        try {
            if ($this->deletingJobType === 'failed') {
                DB::table('failed_jobs')->where('id', $this->deletingJobId)->delete();
                $this->dispatch('notify', message: 'Job fallido eliminado', type: 'success');
                $this->loadStats();
            } else {
                DB::table('jobs')->where('id', $this->deletingJobId)->delete();
                $this->dispatch('notify', message: 'Job eliminado', type: 'success');
            }
            $this->dispatch('job-deleted');
        } catch (\Throwable $e) {
            $this->dispatch('notify', message: 'Error al eliminar: ' . $e->getMessage(), type: 'error');
        }

        $this->reset('deletingJobId', 'deletingJobType');
    }

    public function clearAllFailed()
    {
        DB::table('failed_jobs')->delete();
        $this->dispatch('notify', message: 'Todos los jobs fallidos fueron eliminados', type: 'success');
        $this->loadStats();
    }

    public function showDetails($jobId, $jobType = 'pending')
    {
        $this->selectedJobId = $jobId;

        if ($jobType === 'failed') {
            $job = DB::table('failed_jobs')->where('id', $jobId)->first();
        } else {
            $job = DB::table('jobs')->where('id', $jobId)->first();
        }

        if ($job) {
            $payload = json_decode($job->payload ?? '{}', true);

            // Extraer información del comando
            $commandData = $this->extractCommandData($payload);

            $this->selectedJobDetails = [
                'id' => $job->id,
                'type' => $jobType,
                'queue' => $job->queue,
                'payload' => $payload,
                'displayName' => $payload['displayName'] ?? 'N/A',
                'handler' => $payload['handler'] ?? null,
                'command' => $commandData['command'] ?? null,
                'commandContent' => $commandData['commandContent'] ?? null,
                'data' => $commandData['data'] ?? null,
                'attempts' => $job->attempts ?? 0,
                'failed_at' => $job->failed_at ?? null,
                'exception' => $job->exception ?? null,
                'created_at' => $job->created_at ?? $job->failed_at ?? null,
                'reserved_at' => $job->reserved_at ?? null,
                'available_at' => $job->available_at ?? null,
                'raw_payload' => $job->payload ?? '',
            ];
        }
    }

    private function extractCommandData($payload): array
    {
        $result = [
            'command' => null,
            'commandContent' => null,
            'data' => null,
        ];

        // Intentar extraer del campo 'data'
        if (isset($payload['data'])) {
            $data = $payload['data'];

            // Si es string, decodificar
            if (is_string($data)) {
                $data = @json_decode($data, true);
            }

            if (is_array($data)) {
                // Buscar el nombre del comando
                if (isset($data['commandName'])) {
                    $result['command'] = $data['commandName'];
                }

                // Buscar el contenido serializado del comando
                if (isset($data['command'])) {
                    $serialized = $data['command'];
                    // Intentar deserializar el objeto PHP
                    try {
                        $unserialized = @unserialize($serialized);
                        if ($unserialized !== false) {
                            // Convertir el objeto a array para mostrar
                            $result['commandContent'] = $this->objectToArray($unserialized);
                        } else {
                            // Si no se puede deserializar, mostrar el string formateado
                            $result['commandContent'] = $this->formatSerializedString($serialized);
                        }
                    } catch (\Throwable $e) {
                        $result['commandContent'] = $serialized;
                    }
                }

                // Obtener los parámetros (excluyendo command y commandName)
                $params = $data;
                unset($params['command']);
                unset($params['commandName']);
                if (!empty($params)) {
                    $result['data'] = $params;
                }
            }
        }

        return $result;
    }

    private function objectToArray($obj)
    {
        if (is_object($obj)) {
            $obj = (array)$obj;
        }
        if (is_array($obj)) {
            return array_map(function ($item) {
                return $this->objectToArray($item);
            }, $obj);
        }
        return $obj;
    }

    private function formatSerializedString($str): string
    {
        // Agregar saltos de línea después de ciertos caracteres para mejorar legibilidad
        $formatted = preg_replace('/([";{}])(.)/', '$1' . "\n" . '$2', $str);
        return $formatted;
    }

    private function loadJobs()
    {
        $query = null;

        if ($this->filterStatus === 'pending') {
            $query = DB::table('jobs');
        } elseif ($this->filterStatus === 'failed') {
            $query = DB::table('failed_jobs');
        } else {
            // all: combinar ambas tablas
            $pending = DB::table('jobs')
                ->select('id', 'queue', 'payload', 'available_at', 'created_at', DB::raw("'pending' as status"))
                ->orderBy($this->sortBy, $this->sortDirection)
                ->get();

            $failed = DB::table('failed_jobs')
                ->select('id', 'queue', 'payload', DB::raw("failed_at as available_at"), DB::raw("failed_at as created_at"), DB::raw("'failed' as status"))
                ->orderBy($this->sortBy, $this->sortDirection)
                ->get();

            $collection = $pending->concat($failed)->sortByDesc('id');

            // Paginación manual de colección
            $page = request()->get('page', 1);
            $items = $collection->forPage($page, $this->perPage);

            return new \Illuminate\Pagination\Paginator(
                $items,
                $this->perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        // Aplicar filtros
        if (!empty($this->filterQueue)) {
            $query->where('queue', $this->filterQueue);
        }

        if ($this->filterStatus === 'failed') {
            return $query
                ->orderBy($this->sortBy, $this->sortDirection)
                ->paginate($this->perPage);
        } else {
            return $query
                ->orderBy($this->sortBy, $this->sortDirection)
                ->paginate($this->perPage);
        }
    }

    public function render()
    {
        $this->loadStats();
        $jobs = $this->loadJobs();

        return view('livewire.central.dashboard.jobs-table', [
            'jobs' => $jobs,
            'selectedJobDetails' => $this->selectedJobDetails,
        ]);
    }
}
