<?php

namespace Tests\Unit\Services\Tenant;

use App\Models\Tenant\StudentPlanAssignment;
use App\Services\Tenant\SessionReminderPlanner;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class SessionReminderPlannerTest extends TestCase
{
    public function test_it_counts_unique_training_days_from_snapshot(): void
    {
        $planner = new SessionReminderPlanner();
        $assignment = new StudentPlanAssignment([
            'exercises_snapshot' => [
                ['day' => 1],
                ['day' => 1],
                ['day' => 2],
                ['day' => 3],
            ],
        ]);

        $this->assertSame(3, $planner->resolveTrainingDays($assignment));
    }

    public function test_three_day_plan_requires_two_business_days_since_last_session(): void
    {
        $planner = new SessionReminderPlanner();
        $assignment = new StudentPlanAssignment([
            'exercises_snapshot' => [
                ['day' => 1],
                ['day' => 2],
                ['day' => 3],
            ],
        ]);

        $now = Carbon::parse('2026-02-11 09:00:00'); // Wednesday
        $lastSessionTuesday = Carbon::parse('2026-02-10 08:00:00');
        $lastSessionMonday = Carbon::parse('2026-02-09 08:00:00');

        $this->assertFalse($planner->shouldSendReminderToday($assignment, $lastSessionTuesday, $now));
        $this->assertTrue($planner->shouldSendReminderToday($assignment, $lastSessionMonday, $now));
    }

    public function test_three_day_plan_without_completed_sessions_uses_every_other_business_day(): void
    {
        $planner = new SessionReminderPlanner();
        $assignment = new StudentPlanAssignment([
            'exercises_snapshot' => [
                ['day' => 1],
                ['day' => 2],
                ['day' => 3],
            ],
        ]);
        $attributes = $assignment->getAttributes();
        $attributes['starts_at'] = '2026-02-09 00:00:00'; // Monday
        $assignment->setRawAttributes($attributes);

        $monday = Carbon::parse('2026-02-09 09:00:00');
        $tuesday = Carbon::parse('2026-02-10 09:00:00');
        $wednesday = Carbon::parse('2026-02-11 09:00:00');

        $this->assertTrue($planner->shouldSendReminderToday($assignment, null, $monday));
        $this->assertFalse($planner->shouldSendReminderToday($assignment, null, $tuesday));
        $this->assertTrue($planner->shouldSendReminderToday($assignment, null, $wednesday));
    }

    public function test_four_day_plan_uses_24_hour_fallback(): void
    {
        $planner = new SessionReminderPlanner();
        $assignment = new StudentPlanAssignment([
            'exercises_snapshot' => [
                ['day' => 1],
                ['day' => 2],
                ['day' => 3],
                ['day' => 4],
            ],
        ]);

        $now = Carbon::parse('2026-02-11 09:00:00');

        $this->assertFalse(
            $planner->shouldSendReminderToday($assignment, Carbon::parse('2026-02-10 10:00:00'), $now)
        );

        $this->assertTrue(
            $planner->shouldSendReminderToday($assignment, Carbon::parse('2026-02-10 09:00:00'), $now)
        );
    }

    public function test_five_day_plan_is_daily_on_weekdays(): void
    {
        $planner = new SessionReminderPlanner();
        $assignment = new StudentPlanAssignment([
            'exercises_snapshot' => [
                ['day' => 1],
                ['day' => 2],
                ['day' => 3],
                ['day' => 4],
                ['day' => 5],
            ],
        ]);

        $weekday = Carbon::parse('2026-02-11 09:00:00'); // Wednesday
        $weekend = Carbon::parse('2026-02-14 09:00:00'); // Saturday

        $this->assertTrue($planner->shouldSendReminderToday($assignment, null, $weekday));
        $this->assertFalse($planner->shouldSendReminderToday($assignment, null, $weekend));
    }

    public function test_target_hour_defaults_to_nine_and_clamps_outside_window(): void
    {
        $planner = new SessionReminderPlanner();

        $this->assertSame(9, $planner->resolveTargetHour(null));
        $this->assertSame(18, $planner->resolveTargetHour(Carbon::parse('2026-02-11 18:15:00')));
        $this->assertSame(9, $planner->resolveTargetHour(Carbon::parse('2026-02-11 23:15:00')));
        $this->assertSame(9, $planner->resolveTargetHour(Carbon::parse('2026-02-11 05:15:00')));
    }
}
