<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StudentGamificationProfile extends Model
{
    protected $table = 'student_gamification_profiles';

    protected $fillable = [
        'uuid',
        'student_id',
        'total_xp',
        'current_level',
        'current_tier',
        'active_badge',
        'total_exercises_completed',
        'last_exercise_completed_at',
        'meta',
    ];

    protected $casts = [
        'total_xp' => 'integer',
        'current_level' => 'integer',
        'current_tier' => 'integer',
        'total_exercises_completed' => 'integer',
        'last_exercise_completed_at' => 'date',
        'meta' => 'array',
    ];

    /* -------------------------- Relationships -------------------------- */

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /* -------------------------- Boot -------------------------- */

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /* -------------------------- Accessors & Business Logic -------------------------- */

    /**
     * Obtiene el XP necesario para alcanzar el siguiente nivel
     */
    public function getXpForNextLevelAttribute(): int
    {
        $nextLevel = $this->current_level + 1;
        return $this->calculateXpRequiredForLevel($nextLevel);
    }

    /**
     * Progreso actual dentro del nivel (0.0 a 1.0)
     */
    public function getLevelProgressAttribute(): float
    {
        if ($this->current_level === 0) {
            $xpForLevel1 = 100;
            return min(1.0, $this->total_xp / $xpForLevel1);
        }

        $xpForCurrentLevel = $this->calculateXpRequiredForLevel($this->current_level);
        $xpForNextLevel = $this->xp_for_next_level;

        $xpInCurrentLevel = $this->total_xp - $xpForCurrentLevel;
        $xpNeededForLevel = $xpForNextLevel - $xpForCurrentLevel;

        if ($xpNeededForLevel <= 0) {
            return 1.0;
        }

        return min(1.0, max(0.0, $xpInCurrentLevel / $xpNeededForLevel));
    }

    /**
     * Progreso dentro del nivel en porcentaje (0-100)
     */
    public function getLevelProgressPercentAttribute(): int
    {
        return (int) round($this->level_progress * 100);
    }

    /**
     * Nombre del tier actual
     */
    public function getTierNameAttribute(): string
    {
        return match ($this->current_tier) {
            0 => 'Not Rated',
            1 => 'Principiante',
            2 => 'Aprendiz',
            3 => 'Competente',
            4 => 'Avanzado',
            5 => 'Experto',
            default => 'Not Rated',
        };
    }

    /**
     * Calcula el XP total requerido para alcanzar un nivel específico
     *
     * Fórmula: XP = 100 * (1.15 ^ (level - 1))
     * Redondeado a múltiplos de 10
     *
     * Nivel 0 = 0 XP (estado inicial)
     * Nivel 1 = 100 XP
     * Nivel 2 = 115 XP
     * Nivel 3 = 132 XP
     * etc.
     */
    public static function calculateXpRequiredForLevel(int $level): int
    {
        if ($level <= 0) {
            return 0;
        }

        if ($level === 1) {
            return 100;
        }

        // Progresión exponencial suave con factor 1.15
        $xp = 100 * pow(1.15, $level - 1);

        // Redondear a múltiplos de 10
        return (int) (ceil($xp / 10) * 10);
    }

    /**
     * Calcula el nivel correspondiente a un XP dado
     */
    public static function calculateLevelFromXp(int $xp): int
    {
        if ($xp < 100) {
            return 0;
        }

        // Búsqueda binaria optimizada
        $level = 1;
        while (self::calculateXpRequiredForLevel($level + 1) <= $xp) {
            $level++;

            // Límite de seguridad
            if ($level > 1000) {
                break;
            }
        }

        return $level;
    }

    /**
     * Calcula el tier correspondiente a un nivel dado
     */
    public static function calculateTierFromLevel(int $level): int
    {
        return match (true) {
            $level === 0 => 0,
            $level >= 1 && $level <= 5 => 1,
            $level >= 6 && $level <= 10 => 2,
            $level >= 11 && $level <= 15 => 3,
            $level >= 16 && $level <= 20 => 4,
            $level >= 21 => 5,
            default => 0,
        };
    }

    /**
     * Obtiene el nombre del badge correspondiente a un tier
     */
    public static function getBadgeNameForTier(int $tier): string
    {
        return match ($tier) {
            0 => 'not_rated',
            1 => 'beginner',
            2 => 'apprentice',
            3 => 'competent',
            4 => 'advanced',
            5 => 'expert',
            default => 'not_rated',
        };
    }

    /**
     * Agrega XP y recalcula nivel/tier automáticamente
     */
    public function addXp(int $xp): void
    {
        if ($xp <= 0) {
            return;
        }

        $this->total_xp += $xp;
        $this->recalculateLevelAndTier();
    }

    /**
     * Recalcula nivel y tier basándose en el XP total actual
     */
    public function recalculateLevelAndTier(): void
    {
        $newLevel = self::calculateLevelFromXp($this->total_xp);
        $newTier = self::calculateTierFromLevel($newLevel);

        $tierChanged = $this->current_tier !== $newTier;

        $this->current_level = $newLevel;
        $this->current_tier = $newTier;

        if ($tierChanged) {
            $this->active_badge = self::getBadgeNameForTier($newTier);
        }
    }
}
