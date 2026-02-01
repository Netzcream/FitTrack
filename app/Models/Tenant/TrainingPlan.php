<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TrainingPlan extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'goal',
        'duration',
        'exercises_data',
        'is_active',
        'created_by_ai',
        'student_id',
        'assigned_from',
        'assigned_until',
        'meta',
    ];

    protected $casts = [
        'exercises_data' => 'array',
        'is_active' => 'boolean',
        'created_by_ai' => 'boolean',
        'meta'      => 'array',
        'assigned_from' => 'date',
        'assigned_until' => 'date',
    ];

    /* ---------------- Relationships ---------------- */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /* ---------------- Helpers para exercises_data ---------------- */
    public function addExercise(int $exerciseId, array $data = []): void
    {
        $exercises = $this->exercises_data ?? [];

        $exercises[] = array_merge([
            'exercise_id' => $exerciseId,
            'day' => 1,
            'order' => count($exercises) + 1,
            'detail' => null,
            'notes' => null,
        ], $data);

        $this->exercises_data = $exercises;
    }

    public function getExercisesAttribute()
    {
        if (empty($this->exercises_data)) {
            return collect([]);
        }

        $exerciseIds = collect($this->exercises_data)->pluck('exercise_id')->toArray();
        $exercises = Exercise::whereIn('id', $exerciseIds)->get()->keyBy('id');

        return collect($this->exercises_data)->map(function ($item) use ($exercises) {
            $exercise = $exercises->get($item['exercise_id']);
            return $exercise ? array_merge($item, [
                'name' => $exercise->name,
                'category' => $exercise->category,
                'equipment' => $exercise->equipment,
            ]) : $item;
        });
    }

    /* ---------------- Scopes ---------------- */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;
        $t = "%{$term}%";
        return $q->where(function ($qq) use ($t) {
            $qq->where('name', 'like', $t)
                ->orWhere('description', 'like', $t)
                ->orWhere('goal', 'like', $t);
        });
    }

    public function scopeAssignable(Builder $q): Builder
    {
        // Planes públicos y activos
        return $q->whereNull('student_id')->where('is_active', true);
    }

    public function scopeAssigned(Builder $q): Builder
    {
        // Planes clonados para alumnos
        return $q->whereNotNull('student_id');
    }

    /* ---------------- Boot ---------------- */
    protected static function booted(): void
    {
        // 🔹 Inicialización al crear
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            // Valor por defecto del meta
            $meta = $model->meta ?? [];
            $meta['version'] = $meta['version'] ?? 1.0;
            $meta['origin']  = $meta['origin']  ?? 'new';
            $model->meta = $meta;
        });

        static::saving(function (self $model) {
            $graceDays = 2; // ← cantidad de días de gracia permitidos

            if ($model->is_active && $model->assigned_from && $model->assigned_until && $model->student_id) {
                // Rango ampliado con días de gracia
                $from = $model->assigned_from->copy()->subDays($graceDays);
                $until = $model->assigned_until->copy()->addDays($graceDays);

                $overlap = static::where('student_id', $model->student_id)
                    ->where('id', '!=', $model->id)
                    ->where('is_active', true)
                    ->where(function ($q) use ($from, $until) {
                        $q->whereBetween('assigned_from', [$from, $until])
                            ->orWhereBetween('assigned_until', [$from, $until])
                            ->orWhere(function ($qq) use ($from, $until) {
                                $qq->where('assigned_from', '<=', $from)
                                    ->where('assigned_until', '>=', $until);
                            });
                    })
                    ->exists();

                if ($overlap) {
                    throw new \Exception('Ya existe un plan vigente en ese período (considerando días de gracia).');
                }
            }
        });
    }


    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /* ---------------- Media Library ---------------- */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile()->useDisk('public');
    }

    /* ---------------- Helpers ---------------- */

    /** Copiar ejercicios del plan actual a otro plan destino */
    protected function copyExercisesTo(self $target): void
    {
        // Copiar exercises_data directamente como JSON
        $target->exercises_data = $this->exercises_data;
        $target->save();
    }



    /** Clonar plan como nueva versión (duplicado completo) */
    public function duplicate(): self
    {
        // Replicar sin relaciones cargadas ni UUID
        $clone = $this->replicate(['uuid', 'student_id']);
        $clone->uuid = (string) Str::uuid();
        $clone->is_active = false;

        $meta = $this->meta ?? [];
        $currentVersion = (float) ($meta['version'] ?? 1.0);

        // --- Nueva lógica de versión ---
        if ($this->student_id) {
            // Plan asignado → subir en décimas
            $next = round($currentVersion + 0.1, 1);
            if ($next >= floor($currentVersion) + 1) {
                $next = floor($currentVersion) + 1.0;
            }
        } else {
            // Plan base → subir entero
            $next = floor($currentVersion + 1);
        }

        // --- Ajuste de nombre ---
        if ($this->student_id) {
            // Si es un plan asignado, mostrar versión en nombre
            $baseName = preg_replace('/\(v\d+(\.\d+)?\)$/i', '', $this->name);
            $clone->name = trim(sprintf('%s (v%.1f)', $baseName, $next));
        } else {
            // Si es un plan general, marcar como copia
            $clone->name = $this->name . ' – copia';
        }

        // --- Actualizar meta ---
        $meta['version']     = $next;
        $meta['origin']      = 'duplicate';
        $meta['parent_uuid'] = $this->uuid;
        $clone->meta = $meta;

        $clone->save();

        // Clonar media (solo si existe cover)
        if ($this->hasMedia('cover')) {
            $clone->addMediaFromUrl($this->getFirstMediaUrl('cover'))
                ->toMediaCollection('cover');
        }

        // Copiar ejercicios (pivot limpio)
        $this->copyExercisesTo($clone);

        return $clone;
    }


    /* ---------------- Accessors ---------------- */

    /** Etiqueta de versión formateada, ej: "v1.3" */
    public function getVersionLabelAttribute(): string
    {
        $version = $this->meta['version'] ?? null;

        if (!$version) {
            return '';
        }

        // Asegurar formato con un decimal
        $formatted = number_format((float) $version, 1, '.', '');

        return 'v' . $formatted;
    }



    /** Asignar plan a un alumno (crea subversión) */
    public function assignToStudent(Student $student): self
    {
        $assignment = $this->replicate(['uuid', 'student_id']);
        $assignment->uuid       = (string) Str::uuid();
        $assignment->student_id = $student->id;
        $assignment->is_active  = true;

        $meta = $this->meta ?? [];
        $meta['version']      = round(($meta['version'] ?? 1) + 0.1, 1);
        $meta['origin']       = 'assigned';
        $meta['parent_uuid']  = $this->uuid;
        $meta['assigned_at']  = now();
        $assignment->meta     = $meta;

        $assignment->save();

        // Copiar media si existe
        if ($this->hasMedia('cover')) {
            $assignment->addMediaFromUrl($this->getFirstMediaUrl('cover'))
                ->toMediaCollection('cover');
        }

        // Copiar ejercicios (pivot limpio)
        $this->copyExercisesTo($assignment);

        return $assignment;
    }

    public function __toString(): string
    {
        return $this->name ?? static::class;
    }


    public function getExercisesByDayAttribute()
    {
        // Agrupar exercises_data por día
        return $this->exercises->groupBy(fn($ex) => $ex['day'] ?? 0)->sortKeys();
    }
    public function getIsCurrentAttribute(): bool
    {
        $today = now();
        return $this->is_active
            && (!$this->assigned_from || $this->assigned_from->lte($today))
            && (!$this->assigned_until || $this->assigned_until->isFuture());
    }
}
