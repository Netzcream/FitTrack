<?php

namespace App\Livewire\Central\Dashboard;

use App\Models\DeployLog;
use Livewire\Component;
use Illuminate\Support\Facades\Process;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Artisan;

#[Layout('components.layouts.app')]
class DeployPanel extends Component
{

    public string $status = 'idle';
    public string $output = '';

    public function runDeploy()
    {
        DeployLog::truncate();
        Artisan::call('app:run-deploy');

    }

    public function render()
    {
        return view('livewire.central.dashboard.deploy-panel');
    }
}
