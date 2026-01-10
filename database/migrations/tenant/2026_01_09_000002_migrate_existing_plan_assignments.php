<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('student_plan_assignments')) {
            return;
        }

        // Group plans by student to determine which is active
        $plans = DB::table('training_plans')
            ->whereNotNull('student_id')
            ->orderBy('student_id')
            ->orderByDesc('assigned_from')
            ->orderByDesc('id')
            ->get();

        $processedByStudent = [];

        foreach ($plans as $plan) {
            $meta = json_decode($plan->meta ?? 'null', true) ?: [];

            // Only the latest plan per student should be active
            $isActive = !isset($processedByStudent[$plan->student_id]);
            $processedByStudent[$plan->student_id] = true;

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
                'is_active' => $isActive,
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
        // Do not remove migrated data
    }
};
