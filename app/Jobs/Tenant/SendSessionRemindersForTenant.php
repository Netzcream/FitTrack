<?php

namespace App\Jobs\Tenant;

use App\Enums\PlanAssignmentStatus;
use App\Enums\WorkoutStatus;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentPlanAssignment;
use App\Notifications\SessionReminderNotification;
use App\Services\Tenant\SessionReminderPlanner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSessionRemindersForTenant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $tenantId,
        public bool $force = false
    ) {
    }

    public function handle(SessionReminderPlanner $planner): void
    {
        $initialized = false;

        try {
            if ($this->tenantId !== '') {
                tenancy()->initialize($this->tenantId);
                $initialized = true;
            }

            $now = now();
            $today = $now->toDateString();

            if (! $this->force && $now->isWeekend()) {
                Log::info('[REMINDERS] Saltado por fin de semana', [
                    'tenant_id' => $this->tenantId,
                    'date' => $today,
                ]);

                return;
            }

            $assignments = StudentPlanAssignment::query()
                ->where('status', PlanAssignmentStatus::ACTIVE)
                ->whereDate('starts_at', '<=', $today)
                ->where(function ($query) use ($today) {
                    $query->whereNull('ends_at')
                        ->orWhereDate('ends_at', '>=', $today);
                })
                ->with('student')
                ->get();

            $sent = 0;
            $skipped = 0;
            $processedStudents = [];

            foreach ($assignments as $assignment) {
                $student = $assignment->student;

                if (! $student) {
                    continue;
                }

                if (isset($processedStudents[$student->id])) {
                    continue;
                }

                $processedStudents[$student->id] = true;

                if (
                    ! $student->email
                    || ! $student->is_user_enabled
                    || ! $student->shouldReceiveSessionReminderNotification()
                ) {
                    $skipped++;
                    continue;
                }

                if ($this->alreadyRemindedToday($student->id, $today)) {
                    $skipped++;
                    continue;
                }

                $lastCompletedWorkout = $student->workouts()
                    ->where('status', WorkoutStatus::COMPLETED)
                    ->whereNotNull('completed_at')
                    ->orderByDesc('completed_at')
                    ->first();

                $lastCompletedAt = $lastCompletedWorkout?->completed_at;

                if (! $this->force) {
                    $targetHour = $planner->resolveTargetHour($lastCompletedAt?->copy());

                    if ((int) $now->hour !== $targetHour) {
                        $skipped++;
                        continue;
                    }

                    if (! $planner->shouldSendReminderToday($assignment, $lastCompletedAt?->copy(), $now->copy())) {
                        $skipped++;
                        continue;
                    }
                }

                $student->notify(new SessionReminderNotification(
                    $assignment,
                    $lastCompletedAt?->toIso8601String()
                ));

                $sent++;
            }

            Log::info('[REMINDERS] Proceso completado', [
                'tenant_id' => $this->tenantId,
                'force' => $this->force,
                'assignments_checked' => $assignments->count(),
                'students_processed' => count($processedStudents),
                'sent' => $sent,
                'skipped' => $skipped,
                'hour' => (int) $now->hour,
                'date' => $today,
            ]);
        } catch (\Throwable $exception) {
            Log::error('[REMINDERS] Error al enviar recordatorios de sesion', [
                'tenant_id' => $this->tenantId,
                'force' => $this->force,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        } finally {
            if ($initialized) {
                tenancy()->end();
            }
        }
    }

    private function alreadyRemindedToday(int $studentId, string $date): bool
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', Student::class)
            ->where('notifiable_id', $studentId)
            ->where('type', SessionReminderNotification::class)
            ->whereDate('created_at', $date)
            ->exists();
    }
}
