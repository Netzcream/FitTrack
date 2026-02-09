<?php

namespace App\Services\Tenant;

use App\Events\Tenant\TrainingPlanActivated;
use App\Models\Tenant\Student;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\StudentPlanAssignment;
use App\Models\Tenant\Exercise;
use App\Enums\PlanAssignmentStatus;
use Illuminate\Support\Facades\DB;

class AssignPlanService
{
    /**
     * Assign a plan template to a student.
     * If startNow is true and there's an active plan, it will be cancelled.
     * If startNow is false, the current plan remains active and new one is queued as pending.
     */
    public function assign(TrainingPlan $template, Student $student, ?\Carbon\Carbon $startsAt = null, ?\Carbon\Carbon $endsAt = null, bool $startNow = false): StudentPlanAssignment
    {
        return DB::transaction(function () use ($template, $student, $startsAt, $endsAt, $startNow) {
            $current = $student->planAssignments()->where('status', PlanAssignmentStatus::ACTIVE)->first();

            // Buscar planes futuros pendientes
            $futurePlans = $student->planAssignments()
                ->where('status', PlanAssignmentStatus::PENDING)
                ->where('starts_at', '>', now())
                ->get();

            // Cancelar todos los planes futuros pendientes
            if ($futurePlans->isNotEmpty()) {
                foreach ($futurePlans as $futurePlan) {
                    $futurePlan->update([
                        'status' => PlanAssignmentStatus::CANCELLED,
                        'ends_at' => now(),
                    ]);
                }
            }

            // Determinar el status del nuevo plan
            $newStatus = PlanAssignmentStatus::ACTIVE;

            if ($current) {
                if ($startNow) {
                    // Empezar ya: cancelar el plan actual
                    $current->update([
                        'status' => PlanAssignmentStatus::CANCELLED,
                        'is_active' => false,
                        'ends_at' => now(),
                    ]);
                } else {
                    // Encolar: el nuevo plan queda como pendiente
                    $newStatus = PlanAssignmentStatus::PENDING;
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
                'status' => $newStatus,
                'is_active' => $newStatus === PlanAssignmentStatus::ACTIVE,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);

            $assignment->save();
            $assignment->loadMissing('student');

            DB::afterCommit(function () use ($assignment) {
                TrainingPlanActivated::dispatch(
                    $assignment,
                    'manual',
                    tenant('id') ? (string) tenant('id') : null
                );
            });

            return $assignment;
        });
    }
}
