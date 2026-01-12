<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StudentWeightEntry extends Model
{
    protected $table = 'student_weight_entries';

    protected $fillable = [
        'student_id',
        'weight_kg',
        'source',
        'recorded_at',
        'notes',
        'meta',
        'uuid',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'recorded_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::orderedUuid();
            }
        });
    }

    /**
     * Relación: pertenece a un estudiante
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /* ======================== Accessors ======================== */

    /**
     * Obtener peso en libras (para conveniencia)
     */
    public function getWeightLbsAttribute(): float
    {
        return round($this->weight_kg * 2.20462, 2);
    }

    /**
     * Obtener etiqueta formateada de peso
     */
    public function getFormattedWeightAttribute(): string
    {
        return "{$this->weight_kg} kg ({$this->weight_lbs} lbs)";
    }

    /**
     * Obtener etiqueta de fuente
     */
    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'manual' => 'Manual Entry',
            'scale_device' => 'Smart Scale',
            'api' => 'Third-party API',
            default => $this->source,
        };
    }

    /* ======================== Scopes ======================== */

    /**
     * Filtrar por estudiante
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Ordenar por fecha más reciente
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('recorded_at', 'desc');
    }

    /**
     * Obtener registros desde cierta fecha
     */
    public function scopeSince($query, $date)
    {
        return $query->where('recorded_at', '>=', $date);
    }

    /**
     * Obtener último registro de peso
     */
    public static function latestForStudent($studentId): ?self
    {
        return static::forStudent($studentId)->latest()->first();
    }

    /**
     * Obtener progreso de peso en últimas N semanas
     */
    public function scopeLastWeeks($query, $weeks = 4)
    {
        return $query->since(now()->subWeeks($weeks));
    }

    /* ======================== Methods ======================== */

    /**
     * Calcular cambio de peso desde una fecha
     */
    public static function weightChangeSince($studentId, $date): ?array
    {
        $earliest = static::forStudent($studentId)
            ->since($date)
            ->latest()
            ->last();

        $latest = static::latestForStudent($studentId);

        if (!$earliest || !$latest) {
            return null;
        }

        $change = $latest->weight_kg - $earliest->weight_kg;
        $changePercentage = ($change / $earliest->weight_kg) * 100;

        return [
            'initial_weight_kg' => $earliest->weight_kg,
            'current_weight_kg' => $latest->weight_kg,
            'change_kg' => round($change, 2),
            'change_percentage' => round($changePercentage, 2),
            'change_lbs' => round($change * 2.20462, 2),
            'period_days' => $earliest->recorded_at->diffInDays($latest->recorded_at),
        ];
    }

    /**
     * Calcular promedio de peso en un período
     */
    public static function averageWeightForPeriod($studentId, $startDate, $endDate): ?float
    {
        return static::forStudent($studentId)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->avg('weight_kg');
    }
}
