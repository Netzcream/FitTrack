# Sistema de Gamificación FitTrack

## 📋 Índice

1. [Resumen ejecutivo](#resumen-ejecutivo)
2. [Arquitectura del sistema](#arquitectura-del-sistema)
3. [Base de datos](#base-de-datos)
4. [Modelos](#modelos)
5. [Eventos y listeners](#eventos-y-listeners)
6. [Servicio de gamificación](#servicio-de-gamificación)
7. [Configuración](#configuración)
8. [Cómo usar](#cómo-usar)
9. [Fórmulas y cálculos](#fórmulas-y-cálculos)
10. [Anti-farming](#anti-farming)
11. [Ejemplos de uso](#ejemplos-de-uso)
12. [Testing](#testing)
13. [Próximos pasos](#próximos-pasos)

---

## Resumen ejecutivo

Sistema de gamificación **simple, acumulativo y no punitivo** que incentiva la adherencia del alumno al entrenamiento mediante:

- ✅ **XP (Experience Points)** por ejercicios completados
- ✅ **Niveles** basados en XP acumulado
- ✅ **Tiers** (rangos) que agrupan niveles
- ✅ **Badges** visuales por tier
- ✅ **Anti-farming** mediante validación única por día
- ✅ **Event-driven** con arquitectura desacoplada

### Características clave

- **Individual**: No competitivo
- **Nunca resta puntos**: Solo suma
- **Basado en acciones reales**: No puede manipularse
- **Difícil de explotar**: Validación en BD + lógica
- **No interfiere**: Con métricas clínicas ni planificación

---

## Arquitectura del sistema

```
┌─────────────────────────────────────────────────────┐
│              CUANDO SE COMPLETA UN EJERCICIO         │
└───────────────────┬─────────────────────────────────┘
                    │
                    ▼
        ┌───────────────────────┐
        │ ExerciseCompleted     │ ◄── Evento disparado
        │ (Event)               │
        └───────────┬───────────┘
                    │
                    ▼
        ┌───────────────────────┐
        │ AwardExperiencePoints │ ◄── Listener (queued)
        │ (Listener)            │
        └───────────┬───────────┘
                    │
                    ▼
        ┌───────────────────────┐
        │ GamificationService   │ ◄── Lógica de negocio
        └───────────┬───────────┘
                    │
        ┌───────────┴────────────┐
        ▼                        ▼
┌───────────────┐      ┌────────────────────────┐
│ Anti-farming  │      │ Cálculo XP/Nivel/Tier  │
│ Validation    │      │                        │
└───────┬───────┘      └────────┬───────────────┘
        │                       │
        ▼                       ▼
┌────────────────────────────────────────┐
│ ExerciseCompletionLog                  │
│ (registro único student+exercise+date) │
└────────────────────────────────────────┘
                    │
                    ▼
┌────────────────────────────────────────┐
│ StudentGamificationProfile             │
│ (XP, nivel, tier, badge)               │
└────────────────────────────────────────┘
```

---

## Base de datos

### Tablas creadas

#### 1. `student_gamification_profiles`

Perfil de gamificación por alumno (uno a uno).

```sql
CREATE TABLE student_gamification_profiles (
    id BIGINT UNSIGNED PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE,
    student_id BIGINT UNSIGNED UNIQUE,
    total_xp BIGINT UNSIGNED DEFAULT 0,
    current_level INT UNSIGNED DEFAULT 0,
    current_tier TINYINT UNSIGNED DEFAULT 0,
    active_badge VARCHAR(255) DEFAULT 'not_rated',
    total_exercises_completed INT UNSIGNED DEFAULT 0,
    last_exercise_completed_at DATE NULL,
    meta JSON NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX (current_level),
    INDEX (current_tier),
    INDEX (total_xp)
);
```

**Campos clave:**
- `total_xp`: XP total acumulado (nunca decrece)
- `current_level`: Nivel actual (derivado de total_xp)
- `current_tier`: Tier actual (0-5)
- `active_badge`: Badge visual del tier actual

#### 2. `exercise_completion_logs`

Log de ejercicios completados (anti-farming).

```sql
CREATE TABLE exercise_completion_logs (
    id BIGINT UNSIGNED PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE,
    student_id BIGINT UNSIGNED,
    exercise_id BIGINT UNSIGNED,
    workout_id BIGINT UNSIGNED NULL,
    completed_date DATE,
    xp_earned SMALLINT UNSIGNED,
    exercise_level VARCHAR(255),
    exercise_snapshot JSON NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE,
    FOREIGN KEY (workout_id) REFERENCES workouts(id) ON DELETE SET NULL,
    
    -- CRÍTICO: Índice único anti-farming
    UNIQUE KEY unique_student_exercise_per_day (student_id, exercise_id, completed_date),
    
    INDEX (student_id, completed_date),
    INDEX (workout_id)
);
```

**Índice único crítico:**
```sql
UNIQUE (student_id, exercise_id, completed_date)
```

Este índice garantiza a nivel de base de datos que un alumno **NO** puede completar el mismo ejercicio más de una vez por día.

---

## Modelos

### StudentGamificationProfile

**Ubicación:** `app/Models/Tenant/StudentGamificationProfile.php`

**Métodos principales:**

```php
// Agregar XP y recalcular automáticamente nivel/tier
$profile->addXp(15);

// Recalcular nivel y tier basándose en XP total
$profile->recalculateLevelAndTier();

// Accessors
$profile->xp_for_next_level;      // int
$profile->level_progress;          // float (0.0 - 1.0)
$profile->level_progress_percent;  // int (0-100)
$profile->tier_name;               // string

// Métodos estáticos
StudentGamificationProfile::calculateXpRequiredForLevel(5);  // int
StudentGamificationProfile::calculateLevelFromXp(250);        // int
StudentGamificationProfile::calculateTierFromLevel(12);       // int
StudentGamificationProfile::getBadgeNameForTier(3);          // string
```

### ExerciseCompletionLog

**Ubicación:** `app/Models/Tenant/ExerciseCompletionLog.php`

**Métodos principales:**

```php
// Verificar si ejercicio ya fue completado hoy
ExerciseCompletionLog::wasExerciseCompletedToday($studentId, $exerciseId, $date);

// Obtener XP por dificultad
ExerciseCompletionLog::getXpForExerciseLevel('intermediate'); // 15
```

### Student (extensión)

**Relaciones agregadas:**

```php
$student->gamificationProfile;        // HasOne
$student->exerciseCompletionLogs;     // HasMany
```

---

## Eventos y listeners

### Evento: `ExerciseCompleted`

**Ubicación:** `app/Events/Tenant/ExerciseCompleted.php`

**Disparo:**

```php
use App\Events\Tenant\ExerciseCompleted;

event(new ExerciseCompleted(
    student: $student,
    exercise: $exercise,
    workout: $workout,      // opcional
    completedAt: now()      // opcional
));
```

### Listener: `AwardExperiencePoints`

**Ubicación:** `app/Listeners/Tenant/AwardExperiencePoints.php`

- Implementa `ShouldQueue` (procesamiento asíncrono)
- Llama a `GamificationService::processExerciseCompletion()`
- Captura errores sin bloquear otros listeners

**Registrado en:** `app/Providers/EventServiceProvider.php`

```php
\App\Events\Tenant\ExerciseCompleted::class => [
    \App\Listeners\Tenant\AwardExperiencePoints::class,
],
```

---

## Servicio de gamificación

### GamificationService

**Ubicación:** `app/Services/Tenant/GamificationService.php`

**Métodos principales:**

#### `processExerciseCompletion()`

Procesa el completado de un ejercicio y otorga XP si corresponde.

```php
$service = new GamificationService();

$log = $service->processExerciseCompletion(
    student: $student,
    exercise: $exercise,
    workout: $workout,
    completedAt: now()
);

// Retorna ExerciseCompletionLog o null si ya fue completado hoy
```

**Flujo interno:**

1. ✅ Validar anti-farming (¿ya completado hoy?)
2. ✅ Calcular XP según dificultad
3. ✅ Crear snapshot del ejercicio
4. ✅ **Transacción:**
   - Registrar `ExerciseCompletionLog`
   - Obtener/crear perfil de gamificación
   - Agregar XP y recalcular nivel/tier
   - Actualizar estadísticas
5. ✅ Log de nivel up / tier up

#### `getOrCreateProfile()`

```php
$profile = $service->getOrCreateProfile($student);
```

#### `getProfile()`

```php
$profile = $service->getProfile($student); // puede ser null
```

#### `getStudentStats()`

Retorna array con estadísticas completas:

```php
$stats = $service->getStudentStats($student);

// [
//     'has_profile' => true,
//     'total_xp' => 285,
//     'current_level' => 8,
//     'current_tier' => 2,
//     'tier_name' => 'Aprendiz',
//     'active_badge' => 'apprentice',
//     'total_exercises' => 19,
//     'level_progress' => 65,
//     'xp_for_next_level' => 350,
//     'last_completed' => '2026-01-18',
// ]
```

#### `getRecentCompletions()`

```php
$completions = $service->getRecentCompletions($student, 10);
```

#### `getLevelTable()`

Útil para debugging/admin:

```php
$table = $service->getLevelTable(30);

// [
//     ['level' => 0, 'xp_required' => 0, 'tier' => 0, 'badge' => 'not_rated'],
//     ['level' => 1, 'xp_required' => 100, 'tier' => 1, 'badge' => 'beginner'],
//     ['level' => 2, 'xp_required' => 120, 'tier' => 1, 'badge' => 'beginner'],
//     ...
// ]
```

---

## Configuración

### Archivo: `config/gamification.php`

```php
return [
    // XP por dificultad
    'xp' => [
        'beginner' => 10,
        'intermediate' => 15,
        'advanced' => 20,
    ],
    
    // Progresión de niveles
    'level_progression' => [
        'base_xp' => 100,
        'growth_factor' => 1.15,
        'round_to' => 10,
    ],
    
    // Tiers (rangos)
    'tiers' => [
        0 => ['name' => 'Not Rated', 'levels' => [0], 'badge' => 'not_rated'],
        1 => ['name' => 'Principiante', 'levels' => [1-5], 'badge' => 'beginner'],
        2 => ['name' => 'Aprendiz', 'levels' => [6-10], 'badge' => 'apprentice'],
        3 => ['name' => 'Competente', 'levels' => [11-15], 'badge' => 'competent'],
        4 => ['name' => 'Avanzado', 'levels' => [16-20], 'badge' => 'advanced'],
        5 => ['name' => 'Experto', 'levels' => [21+], 'badge' => 'expert'],
    ],
    
    // Anti-farming
    'anti_farming' => [
        'exercise_cooldown_hours' => 24,
        'log_blocked_attempts' => true,
    ],
    
    // Features futuras
    'features' => [
        'streaks' => false,
        'achievements' => false,
        'leaderboards' => false,
        'multipliers' => false,
    ],
];
```

---

## Cómo usar

### Migrar las tablas

```bash
# Solo para tenant databases
php artisan tenants:migrate
```

### Disparar evento al completar ejercicio

Ejemplo en un controller o Livewire component:

```php
use App\Events\Tenant\ExerciseCompleted;

// Cuando el alumno marca un ejercicio como completado
public function markExerciseAsCompleted($exerciseId)
{
    $exercise = Exercise::findOrFail($exerciseId);
    $student = auth()->user()->student; // o según tu lógica
    $workout = Workout::find($workoutId); // opcional
    
    // Disparar evento
    event(new ExerciseCompleted(
        student: $student,
        exercise: $exercise,
        workout: $workout
    ));
    
    // El listener se encargará del resto
}
```

### Obtener stats del alumno

```php
use App\Services\Tenant\GamificationService;

$service = new GamificationService();
$stats = $service->getStudentStats($student);

// Usar en vista
return view('student.profile', compact('stats'));
```

### Mostrar progreso en vista

```blade
@if($stats['has_profile'])
    <div class="gamification-card">
        <h3>{{ __('gamification.your_stats') }}</h3>
        
        <div class="level-info">
            <span class="badge badge-{{ $stats['active_badge'] }}">
                {{ $stats['tier_name'] }}
            </span>
            <p>{{ __('gamification.level') }} {{ $stats['current_level'] }}</p>
        </div>
        
        <div class="progress-bar">
            <div class="progress" style="width: {{ $stats['level_progress'] }}%"></div>
        </div>
        
        <p class="text-sm">
            {{ $stats['total_xp'] }} / {{ $stats['xp_for_next_level'] }} XP
        </p>
        
        <p class="text-muted">
            {{ $stats['total_exercises'] }} {{ __('gamification.exercises_completed') }}
        </p>
    </div>
@endif
```

---

## Fórmulas y cálculos

### XP por dificultad

| Dificultad | XP |
|------------|-----|
| Beginner   | 10  |
| Intermediate | 15 |
| Advanced   | 20  |

### Niveles

**Fórmula:**

```
XP_required(level) = 100 × (1.15 ^ (level - 1))
```

Redondeado a múltiplos de 10.

**Ejemplos:**

```
Nivel 0:  0 XP    (estado inicial)
Nivel 1:  100 XP
Nivel 2:  120 XP
Nivel 3:  140 XP
Nivel 4:  160 XP
Nivel 5:  180 XP
Nivel 10: 390 XP
Nivel 15: 760 XP
Nivel 20: 1480 XP
Nivel 25: 2890 XP
Nivel 30: 5640 XP
```

### Tiers

Los niveles se agrupan en tiers:

| Tier | Niveles | Nombre | Badge |
|------|---------|--------|-------|
| 0 | 0 | Not Rated | not_rated |
| 1 | 1-5 | Principiante | beginner |
| 2 | 6-10 | Aprendiz | apprentice |
| 3 | 11-15 | Competente | competent |
| 4 | 16-20 | Avanzado | advanced |
| 5 | 21+ | Experto | expert |

---

## Anti-farming

### Regla principal

> **Un mismo ejercicio NO puede otorgar puntos más de una vez por día por alumno.**

### Implementación

**1. Validación en BD (CRÍTICO):**

```sql
UNIQUE KEY unique_student_exercise_per_day (student_id, exercise_id, completed_date)
```

Si se intenta insertar un registro duplicado, la BD rechaza la operación.

**2. Validación en lógica:**

```php
if ($this->wasExerciseCompletedToday($student->id, $exercise->id, $completedAt)) {
    return null; // No otorgar XP
}
```

**3. Log de intentos bloqueados:**

```php
Log::info('Ejercicio ya completado hoy (anti-farming)', [
    'student_id' => $student->id,
    'exercise_id' => $exercise->id,
    'date' => $completedDate,
]);
```

### Lo que se permite

✅ Repetir el mismo ejercicio **otro día**  
✅ Completar **ejercicios distintos** el mismo día  
✅ Completar el mismo ejercicio en múltiples workouts (solo cuenta el primero)

### Lo que NO se permite

❌ Repetir el mismo ejercicio el mismo día para ganar XP  
❌ Manipulación desde frontend (validación en backend)  
❌ Creación manual de eventos duplicados

---

## Ejemplos de uso

### Ejemplo 1: Completar ejercicio desde Livewire

```php
namespace App\Livewire\Tenant\Workouts;

use Livewire\Component;
use App\Events\Tenant\ExerciseCompleted;

class WorkoutSession extends Component
{
    public $workout;
    public $exercises = [];
    
    public function completeExercise($exerciseId)
    {
        $exercise = Exercise::find($exerciseId);
        $student = auth()->user()->student;
        
        // Marcar como completado en tu lógica de negocio
        // ...
        
        // Disparar evento de gamificación
        event(new ExerciseCompleted(
            student: $student,
            exercise: $exercise,
            workout: $this->workout
        ));
        
        session()->flash('message', __('gamification.exercise_completed'));
    }
}
```

### Ejemplo 2: Mostrar badge en perfil

```blade
@php
    $gamification = app(App\Services\Tenant\GamificationService::class);
    $stats = $gamification->getStudentStats($student);
@endphp

<div class="profile-header">
    <img src="{{ $student->avatar_url }}" alt="{{ $student->full_name }}">
    
    @if($stats['has_profile'])
        <div class="badge-container">
            <span class="badge-tier-{{ $stats['current_tier'] }}">
                {{ __('gamification.tier_' . $stats['current_tier']) }}
            </span>
            <p class="level">Nivel {{ $stats['current_level'] }}</p>
        </div>
    @endif
</div>
```

### Ejemplo 3: Crear perfil manualmente

```php
use App\Services\Tenant\GamificationService;

$service = new GamificationService();
$profile = $service->getOrCreateProfile($student);

// El perfil se crea automáticamente la primera vez que se completa un ejercicio
// pero si necesitas crearlo manualmente:
```

### Ejemplo 4: Consultar historial

```php
$service = new GamificationService();
$recentCompletions = $service->getRecentCompletions($student, 20);

foreach ($recentCompletions as $log) {
    echo "{$log->exercise->name} - {$log->xp_earned} XP - {$log->completed_date}\n";
}
```

---

## Testing

### Test unitarios sugeridos

```php
// tests/Unit/GamificationTest.php

public function test_xp_calculation_by_difficulty()
{
    $this->assertEquals(10, ExerciseCompletionLog::getXpForExerciseLevel('beginner'));
    $this->assertEquals(15, ExerciseCompletionLog::getXpForExerciseLevel('intermediate'));
    $this->assertEquals(20, ExerciseCompletionLog::getXpForExerciseLevel('advanced'));
}

public function test_level_calculation_from_xp()
{
    $this->assertEquals(0, StudentGamificationProfile::calculateLevelFromXp(0));
    $this->assertEquals(0, StudentGamificationProfile::calculateLevelFromXp(99));
    $this->assertEquals(1, StudentGamificationProfile::calculateLevelFromXp(100));
    $this->assertEquals(2, StudentGamificationProfile::calculateLevelFromXp(120));
}

public function test_tier_calculation_from_level()
{
    $this->assertEquals(0, StudentGamificationProfile::calculateTierFromLevel(0));
    $this->assertEquals(1, StudentGamificationProfile::calculateTierFromLevel(3));
    $this->assertEquals(2, StudentGamificationProfile::calculateTierFromLevel(8));
    $this->assertEquals(5, StudentGamificationProfile::calculateTierFromLevel(25));
}

public function test_cannot_complete_same_exercise_twice_same_day()
{
    $student = Student::factory()->create();
    $exercise = Exercise::factory()->create(['level' => 'beginner']);
    $service = new GamificationService();
    
    // Primera vez: debe crear log
    $log1 = $service->processExerciseCompletion($student, $exercise);
    $this->assertNotNull($log1);
    
    // Segunda vez mismo día: debe retornar null
    $log2 = $service->processExerciseCompletion($student, $exercise);
    $this->assertNull($log2);
}

public function test_can_complete_same_exercise_different_days()
{
    $student = Student::factory()->create();
    $exercise = Exercise::factory()->create(['level' => 'beginner']);
    $service = new GamificationService();
    
    // Día 1
    $log1 = $service->processExerciseCompletion($student, $exercise, null, now());
    $this->assertNotNull($log1);
    
    // Día 2
    $log2 = $service->processExerciseCompletion($student, $exercise, null, now()->addDay());
    $this->assertNotNull($log2);
    
    $profile = $service->getProfile($student);
    $this->assertEquals(20, $profile->total_xp); // 10 + 10
}
```

### Test de integración

```php
// tests/Feature/GamificationFeatureTest.php

public function test_completing_exercise_awards_xp_and_levels_up()
{
    $student = Student::factory()->create();
    $exercise = Exercise::factory()->create(['level' => 'beginner']);
    
    // Completar ejercicio 10 veces (diferentes días simulados)
    for ($i = 0; $i < 10; $i++) {
        $date = now()->addDays($i);
        event(new ExerciseCompleted($student, $exercise, null, $date));
    }
    
    // Procesar queue
    Queue::fake();
    
    $service = new GamificationService();
    $profile = $service->getProfile($student);
    
    $this->assertEquals(100, $profile->total_xp); // 10 ejercicios × 10 XP
    $this->assertEquals(1, $profile->current_level);
    $this->assertEquals(1, $profile->current_tier);
}
```

---

## Próximos pasos

### Funcionalidades futuras (NO implementadas aún)

1. **Streaks (rachas)**
   - Días consecutivos completando ejercicios
   - Bonus por mantener racha
   - Reset de racha (con gracia de 1 día)

2. **Achievements (logros)**
   - Logros especiales por hitos
   - "Primera semana completa"
   - "100 ejercicios completados"
   - "30 días consecutivos"

3. **Leaderboards (rankings)**
   - Opcional por gimnasio/entrenador
   - Solo entre alumnos que opten por participar
   - Rankings semanales/mensuales

4. **Multiplicadores**
   - Eventos especiales
   - Bonus por completar rutinas completas
   - XP doble en días especiales

5. **Recompensas simbólicas**
   - Desbloqueo de badges especiales
   - Títulos personalizados
   - Avatares/marcos

### UI/UX pendiente

- Widget de gamificación en dashboard del alumno
- Animaciones de level up
- Notificaciones visuales de XP ganado
- Gráficos de progreso histórico
- Tabla de niveles públicos

### Integraciones

- Mobile API: endpoints de gamificación
- Notificaciones push al subir de nivel
- Email semanal con resumen de progreso

---

## Comandos útiles

```bash
# Migrar tablas de gamificación
php artisan tenants:migrate

# Crear perfil para todos los estudiantes existentes (si es necesario)
php artisan tinker
>>> $students = App\Models\Tenant\Student::all();
>>> foreach ($students as $s) { (new App\Services\Tenant\GamificationService())->getOrCreateProfile($s); }

# Ver tabla de niveles
php artisan tinker
>>> (new App\Services\Tenant\GamificationService())->getLevelTable(30);

# Limpiar queue de jobs
php artisan queue:work --once

# Ver logs de gamificación
tail -f storage/logs/laravel.log | grep -i "nivel\|tier\|xp"
```

---

## Troubleshooting

### El evento no se dispara

✅ Verificar que el evento está registrado en `EventServiceProvider`  
✅ Verificar que el listener está en la cola correcta  
✅ Ejecutar `php artisan queue:work`

### No se otorga XP

✅ Verificar que el ejercicio tiene un `level` válido  
✅ Verificar que no fue completado hoy (revisar logs)  
✅ Verificar tenancy (¿estás en la BD correcta?)

### Error de unique constraint

✅ Es esperado si se intenta completar 2 veces el mismo día  
✅ Revisar lógica de frontend para prevenir doble submit  
✅ Verificar que la fecha se está calculando correctamente

---

## Soporte

Para más información:

- Código fuente: `app/Models/Tenant/StudentGamificationProfile.php`
- Servicio: `app/Services/Tenant/GamificationService.php`
- Config: `config/gamification.php`
- Traducciones: `resources/lang/*/gamification.php`

---

**Versión:** 1.0.0  
**Fecha:** 18 de enero, 2026  
**Autor:** FitTrack Development Team
