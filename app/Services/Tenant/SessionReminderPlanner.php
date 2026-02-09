<?php

namespace App\Services\Tenant;

use App\Models\Tenant\StudentPlanAssignment;
use Carbon\Carbon;

class SessionReminderPlanner
{
    public function resolveTrainingDays(StudentPlanAssignment $assignment): int
    {
        $days = collect($assignment->exercises_snapshot ?? [])
            ->pluck('day')
            ->filter(fn ($day) => is_numeric($day))
            ->map(fn ($day) => (int) $day)
            ->unique()
            ->count();

        return max(0, $days);
    }

    public function resolveTargetHour(?Carbon $lastCompletedAt): int
    {
        if ($lastCompletedAt === null) {
            return 9;
        }

        $hour = (int) $lastCompletedAt->hour;

        if ($hour < 7 || $hour > 21) {
            return 9;
        }

        return $hour;
    }

    public function shouldSendReminderToday(
        StudentPlanAssignment $assignment,
        ?Carbon $lastCompletedAt,
        Carbon $now
    ): bool {
        if ($now->isWeekend()) {
            return false;
        }

        $trainingDays = $this->resolveTrainingDays($assignment);

        // Plan 5+ dias: recordatorio diario (solo lunes-viernes).
        if ($trainingDays >= 5) {
            return true;
        }

        // Plan 4 dias: fallback de +24h desde ultima sesion.
        if ($trainingDays === 4) {
            if ($lastCompletedAt === null) {
                return true;
            }

            return $lastCompletedAt->copy()->addHours(24)->lessThanOrEqualTo($now);
        }

        // Plan 3 dias: dia por medio habil (2 dias habiles desde ultima sesion).
        if ($trainingDays === 3) {
            if ($lastCompletedAt === null) {
                $reference = $this->resolveAssignmentStart($assignment);

                if (! $reference) {
                    return true;
                }

                $businessDaysSinceStart = $this->businessDaysSince($reference, $now);

                if ($businessDaysSinceStart === 0) {
                    return true;
                }

                return $businessDaysSinceStart % 2 === 0;
            }

            return $this->businessDaysSince($lastCompletedAt, $now) >= 2;
        }

        // Fallback general para planes de 1-2 dias o sin estructura.
        if ($lastCompletedAt === null) {
            return true;
        }

        return $lastCompletedAt->copy()->addHours(24)->lessThanOrEqualTo($now);
    }

    public function businessDaysSince(Carbon $from, Carbon $to): int
    {
        $start = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        $count = 0;
        $cursor = $start->copy()->addDay();

        while ($cursor->lessThanOrEqualTo($end)) {
            if (! $cursor->isWeekend()) {
                $count++;
            }

            $cursor->addDay();
        }

        return $count;
    }

    private function resolveAssignmentStart(StudentPlanAssignment $assignment): ?Carbon
    {
        $rawValue = $assignment->getAttributes()['starts_at'] ?? null;

        if ($rawValue instanceof Carbon) {
            return $rawValue->copy();
        }

        if (is_string($rawValue) && trim($rawValue) !== '') {
            return Carbon::parse($rawValue);
        }

        try {
            $castValue = $assignment->starts_at;

            if ($castValue instanceof Carbon) {
                return $castValue->copy();
            }

            if (is_string($castValue) && trim($castValue) !== '') {
                return Carbon::parse($castValue);
            }
        } catch (\Throwable $exception) {
            return null;
        }

        return null;
    }
}
