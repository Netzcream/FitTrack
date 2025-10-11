<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkoutSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'plan_workout_id',
        'plan_block_id',
        'scheduled_date',
        'status',
        'started_at',
        'ended_at',
        'duration_minutes',
        'session_rpe',
        'meta',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'meta' => 'array',
    ];

    // === Relaciones ===
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function planWorkout(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant\Exercise\ExercisePlanWorkout::class, 'plan_workout_id');
    }

    public function planBlock(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant\Exercise\ExercisePlanBlock::class, 'plan_block_id');
    }

    public function sets(): HasMany
    {
        return $this->hasMany(WorkoutSessionSet::class, 'workout_session_id');
    }

    // === Helpers ===
    public function start(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'ended_at' => now(),
            'duration_minutes' => $this->started_at ? now()->diffInMinutes($this->started_at) : null,
        ]);
    }

    public static function ensureNextForStudent(int $studentId): ?self
    {
        $assignment = \App\Models\Tenant\Exercise\ExercisePlanAssignment::query()
            ->where('student_id', $studentId)
            ->where('is_active', true)
            ->where('status', 'active')
            ->latest('start_date') // ← preferimos la más reciente
            ->first();

        if (!$assignment || !$assignment->plan) {
            return null;
        }

        $plan = $assignment->plan;

        self::where('student_id', $studentId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereHas('planWorkout', fn($q) => $q->where('plan_id', '<>', $plan->id))
            ->update([
                'status' => 'completed',
                'updated_at' => now(),
            ]);



        if (!$plan) {
            return null;
        }

        // 1️⃣ Buscar si ya hay una sesión pendiente o en curso

        $existing = self::with('sets')
            ->where('student_id', $studentId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('scheduled_date')
            ->first();

        if ($existing) {
            return $existing;
        }

        $doneWorkoutIds = self::where('student_id', $studentId)
            ->whereHas('planWorkout', fn($q) => $q->where('plan_id', $plan->id))
            ->pluck('plan_workout_id')
            ->toArray();

        $nextWorkout = \App\Models\Tenant\Exercise\ExercisePlanWorkout::query()
            ->where('plan_id', $plan->id)
            ->whereNotIn('id', $doneWorkoutIds)
            ->whereHas('blocks.items')
            ->orderBy('week_index')
            ->orderBy('day_index')
            ->orderBy('order')
            ->first();


        if (!$nextWorkout) {
            $nextWorkout = \App\Models\Tenant\Exercise\ExercisePlanWorkout::query()
                ->where('plan_id', $plan->id)
                ->whereHas('blocks.items')
                ->orderBy('week_index')
                ->orderBy('day_index')
                ->orderBy('order')
                ->first();
        }

        if (!$nextWorkout) {
            return null;
        }

        $session = self::create([
            'student_id' => $studentId,
            'plan_workout_id' => $nextWorkout->id,
            'scheduled_date' => today(),
            'status' => 'pending',
        ]);

        foreach ($nextWorkout->blocks()->with('items')->get() as $block) {
            foreach ($block->items as $item) {
                for ($i = 1; $i <= ($item->sets ?? 1); $i++) {
                    \App\Models\Tenant\WorkoutSessionSet::create([
                        'workout_session_id' => $session->id,
                        'plan_item_id' => $item->id,
                        'set_number' => $i,
                        'target_reps' => $item->reps ?? $item->reps_max,
                        'target_rest_sec' => $item->rest_sec ?? 60,
                    ]);
                }
            }
        }

        return $session->load(['sets', 'planWorkout.blocks.items.exercise']);
    }
}
