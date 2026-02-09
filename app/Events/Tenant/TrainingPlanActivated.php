<?php

namespace App\Events\Tenant;

use App\Models\Tenant\StudentPlanAssignment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrainingPlanActivated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public StudentPlanAssignment $assignment,
        public string $activationType, // 'manual' o 'automatic'
        public ?string $tenantId = null
    ) {
    }
}
