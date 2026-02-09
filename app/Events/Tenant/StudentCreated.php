<?php

namespace App\Events\Tenant;

use App\Models\Tenant\Student;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Student $student,
        public ?string $createdBy = null,
        public ?string $registrationUrl = null,
        public ?string $tenantId = null
    ) {
    }
}
