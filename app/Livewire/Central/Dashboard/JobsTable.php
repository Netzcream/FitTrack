<?php

namespace App\Livewire\Central\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class JobsTable extends Component
{
    public $jobs;


    public function runNow($jobId)
    {
        DB::table('jobs')
            ->where('id', $jobId)
            ->update([
                'available_at' => now()->timestamp,
                'reserved_at' => null
            ]);

        $this->dispatch('notify', message: 'Job enviado a ejecución inmediata');
    }


    public function render()
    {
        $this->jobs = DB::table('jobs')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'queue', 'payload', 'available_at', 'created_at']);


        return view('livewire.central.dashboard.jobs-table');
    }
}
