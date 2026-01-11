<?php

namespace App\Events\Tenant;

use App\Models\Tenant\StudentPlanAssignment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrainingPlanExpiredWithoutReplacement
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public StudentPlanAssignment $expiredAssignment
    ) {
    }
}
