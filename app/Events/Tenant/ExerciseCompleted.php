<?php

namespace App\Events\Tenant;

use App\Models\Tenant\Student;
use App\Models\Tenant\Exercise;
use App\Models\Tenant\Workout;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento disparado cuando un alumno completa un ejercicio
 *
 * Este es el evento fuente del sistema de gamificación.
 * Se dispara cuando un ejercicio es marcado como completado dentro de una sesión.
 */
class ExerciseCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Student $student,
        public Exercise $exercise,
        public ?Workout $workout = null,
        public ?\Carbon\Carbon $completedAt = null,
    ) {
        $this->completedAt = $completedAt ?? now();
    }
}
