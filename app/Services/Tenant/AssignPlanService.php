<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Student;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\StudentPlanAssignment;
use App\Models\Tenant\Exercise;
use Illuminate\Support\Facades\DB;

class AssignPlanService
{
    /**
     * Assign a plan template to a student.
     * If startNow is true and there's an active plan, it will be deactivated.
     * If startNow is false, the current plan remains active and new one is queued.
     */
    public function assign(TrainingPlan $template, Student $student, ?\Carbon\Carbon $startsAt = null, ?\Carbon\Carbon $endsAt = null, bool $startNow = false): StudentPlanAssignment
    {
        return DB::transaction(function () use ($template, $student, $startsAt, $endsAt, $startNow) {
            $current = $student->planAssignments()->where('is_active', true)->first();

            // Solo desactivar el plan actual si startNow es true
            $isActive = true;
            if ($current) {
                if ($startNow) {
                    // Empezar ya: desactivar el plan actual
                    $current->update([
                        'is_active' => false,
                        'ends_at' => now(),
                    ]);
                } else {
                    // Encolar: el nuevo plan no estará activo hasta su fecha de inicio
                    $isActive = false;
                }
            }

            // Create new assignment snapshot
            $snapshot = $template->exercises_data ?? [];

            if (!empty($snapshot)) {
                $exerciseMap = Exercise::whereIn('id', collect($snapshot)->pluck('exercise_id')->filter()->unique())
                    ->get(['id', 'name'])
                    ->keyBy('id');

                $snapshot = collect($snapshot)->map(function ($item) use ($exerciseMap) {
                    if (empty($item['name']) && !empty($item['exercise_id']) && $exerciseMap->has($item['exercise_id'])) {
                        $item['name'] = $exerciseMap[$item['exercise_id']]->name;
                    }
                    return $item;
                })->values()->toArray();
            }

            $assignment = new StudentPlanAssignment([
                'student_id' => $student->id,
                'training_plan_id' => $template->id,
                'name' => $template->name,
                'meta' => [
                    'version' => ($template->meta['version'] ?? 1.0),
                    'origin' => 'assigned',
                    'parent_uuid' => $template->uuid,
                ],
                'exercises_snapshot' => $snapshot,
                'is_active' => $isActive,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);

            $assignment->save();

            return $assignment;
        });
    }
}
