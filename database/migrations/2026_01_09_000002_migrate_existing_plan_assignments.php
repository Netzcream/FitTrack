<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing TrainingPlan rows that have student_id (old model) to assignments
        if (!DB::getSchemaBuilder()->hasTable('student_plan_assignments')) {
            return;
        }

        $plans = DB::table('training_plans')
            ->whereNotNull('student_id')
            ->get();

        foreach ($plans as $plan) {
            $meta = json_decode($plan->meta ?? 'null', true) ?: [];

            DB::table('student_plan_assignments')->insert([
                'uuid' => (string) Str::orderedUuid(),
                'student_id' => $plan->student_id,
                'training_plan_id' => $plan->id,
                'name' => $plan->name,
                'meta' => json_encode([
                    'version' => $meta['version'] ?? 1.0,
                    'origin' => 'migrated',
                    'parent_uuid' => $meta['parent_uuid'] ?? $plan->uuid,
                ]),
                'exercises_snapshot' => $plan->exercises_data,
                'is_active' => (bool) $plan->is_active,
                'starts_at' => $plan->assigned_from,
                'ends_at' => $plan->assigned_until,
                'overrides' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // No-op: do not delete migrated data
    }
};
