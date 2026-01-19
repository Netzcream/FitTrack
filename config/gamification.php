<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gamification Configuration
    |--------------------------------------------------------------------------
    |
    | Sistema de gamificación para FitTrack
    | Configura niveles, tiers, XP y badges
    |
    */

    /*
    |--------------------------------------------------------------------------
    | XP (Experience Points) por dificultad de ejercicio
    |--------------------------------------------------------------------------
    */
    'xp' => [
        'beginner' => 10,
        'intermediate' => 15,
        'advanced' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Progresión de niveles
    |--------------------------------------------------------------------------
    |
    | Factor de crecimiento exponencial para niveles
    | Nivel 1 = 100 XP
    | Nivel N = 100 * (factor ^ (N - 1)) redondeado a múltiplo de 10
    |
    */
    'level_progression' => [
        'base_xp' => 100,           // XP para alcanzar nivel 1
        'growth_factor' => 1.15,    // Factor exponencial (15% de incremento)
        'round_to' => 10,           // Redondear XP a múltiplos de este valor
    ],

    /*
    |--------------------------------------------------------------------------
    | Tiers (Rangos de niveles)
    |--------------------------------------------------------------------------
    |
    | Los niveles se agrupan en tiers que definen el badge visible
    |
    */
    'tiers' => [
        0 => [
            'name' => 'Not Rated',
            'levels' => [0],
            'badge' => 'not_rated',
            'icon' => '🥚', // Conceptual
        ],
        1 => [
            'name' => 'Principiante',
            'levels' => [1, 2, 3, 4, 5],
            'badge' => 'beginner',
            'icon' => '🐢',
        ],
        2 => [
            'name' => 'Aprendiz',
            'levels' => [6, 7, 8, 9, 10],
            'badge' => 'apprentice',
            'icon' => '🐕',
        ],
        3 => [
            'name' => 'Competente',
            'levels' => [11, 12, 13, 14, 15],
            'badge' => 'competent',
            'icon' => '🐅',
        ],
        4 => [
            'name' => 'Avanzado',
            'levels' => [16, 17, 18, 19, 20],
            'badge' => 'advanced',
            'icon' => '🐺',
        ],
        5 => [
            'name' => 'Experto',
            'levels' => [21], // 21+
            'badge' => 'expert',
            'icon' => '🦅',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Badges
    |--------------------------------------------------------------------------
    |
    | Configuración visual de badges
    |
    */
    'badges' => [
        'not_rated' => [
            'name' => 'Not Rated',
            'description' => 'Inicia tu viaje',
            'color' => 'gray',
        ],
        'beginner' => [
            'name' => 'Principiante',
            'description' => 'Los primeros pasos',
            'color' => 'green',
        ],
        'apprentice' => [
            'name' => 'Aprendiz',
            'description' => 'Aprendiendo activamente',
            'color' => 'blue',
        ],
        'competent' => [
            'name' => 'Competente',
            'description' => 'Control y fuerza',
            'color' => 'yellow',
        ],
        'advanced' => [
            'name' => 'Avanzado',
            'description' => 'Experiencia consolidada',
            'color' => 'purple',
        ],
        'expert' => [
            'name' => 'Experto',
            'description' => 'Maestría alcanzada',
            'color' => 'red',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Anti-Farming Rules
    |--------------------------------------------------------------------------
    |
    | Reglas para prevenir explotación del sistema
    |
    */
    'anti_farming' => [
        // Un ejercicio solo da XP 1 vez por día por alumno
        'exercise_cooldown_hours' => 24,

        // Log de intentos bloqueados (para auditoría)
        'log_blocked_attempts' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Features (Extensibilidad futura)
    |--------------------------------------------------------------------------
    |
    | Activar/desactivar características opcionales
    |
    */
    'features' => [
        'streaks' => false,             // Rachas consecutivas (futuro)
        'achievements' => false,        // Logros especiales (futuro)
        'leaderboards' => false,        // Rankings (futuro)
        'multipliers' => false,         // Bonus temporales (futuro)
    ],

];
