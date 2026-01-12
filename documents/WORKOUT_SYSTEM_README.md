# 📋 Workout System - Estructura Completa

## Overview
Sistema de entrenamiento crítico que vincula **Student** → **StudentPlanAssignment** → **Workout** → **Exercises** con soporte para historial de peso e historial de sesiones.

---

## 📊 Modelos y Relaciones

### `Student`
```php
$student->workouts()              // HasMany Workout
$student->weightEntries()          // HasMany StudentWeightEntry
$student->latestWeight()           // HasOne (latest weight entry)
$student->planAssignments()        // HasMany StudentPlanAssignment
$student->currentPlanAssignment()  // HasOne (active status)
```

### `StudentPlanAssignment`
```php
$assignment->student()             // BelongsTo Student
$assignment->plan()                // BelongsTo TrainingPlan
$assignment->workouts()            // HasMany Workout
$assignment->exercises_snapshot    // array - snapshot de ejercicios por día
```

### `Workout`
```php
$workout->student()                // BelongsTo Student
$workout->planAssignment()         // BelongsTo StudentPlanAssignment
$workout->status                   // WorkoutStatus enum (pending|in_progress|completed|skipped)
$workout->exercises_data           // array - ejercicios ejecutados del día
$workout->meta                     // array - survey (fatiga, RPE, dolor, mood)
```

### `StudentWeightEntry`
```php
$entry->student()                  // BelongsTo Student
$entry->weight_kg                  // decimal (5,2)
$entry->recorded_at                // datetime
$entry->source                     // 'manual' | 'scale_device' | 'api'
$entry->meta                       // array - metadata opcional
```

---

## 🔄 Migraciones

### `2026_01_11_000001_create_workouts_table`
**Campos principales:**
- `uuid` (PK)
- `student_id` (FK)
- `student_plan_assignment_id` (FK)
- `plan_day` (1..N) - día del plan a ejecutar
- `sequence_index` - índice global de sesiones (para progreso)
- `cycle_index` - ciclo actual (si repite el plan)
- `started_at`, `completed_at`, `duration_minutes`
- `status` (enum: pending, in_progress, completed, skipped)
- `rating` (1-5)
- `notes`, `exercises_data`, `meta`

**Índices:**
```
[student_id, status]
[student_plan_assignment_id, completed_at]
[student_id, completed_at]
[status]
```

### `2026_01_11_000002_create_student_weight_entries_table`
**Campos principales:**
- `uuid` (PK)
- `student_id` (FK)
- `weight_kg` (decimal 5,2)
- `recorded_at` (datetime)
- `source` ('manual' | 'scale_device' | 'api')
- `notes`, `meta`

**Índices:**
```
[student_id, recorded_at]
[recorded_at]
```

---

## 🎯 Servicios de Orquestación

### `WorkoutOrchestrationService`
Gestiona el flujo completo de workouts. **Métodos principales:**

#### 1. **resolveActivePlan(Student)**
```php
$plan = $service->resolveActivePlan($student);
// Retorna StudentPlanAssignment con status=ACTIVE y fechas válidas
// o null si no hay plan activo
```

#### 2. **getNextPlanDay(StudentPlanAssignment)**
```php
$day = $service->getNextPlanDay($assignment);
// Calcula: (workouts_completados % total_dias) + 1
// Ej: Si hay 5 ejercicios y completó 7, retorna día 3
```

#### 3. **getCurrentCycle(StudentPlanAssignment)**
```php
$cycle = $service->getCurrentCycle($assignment);
// Calcula: floor(workouts_completados / total_dias) + 1
```

#### 4. **getOrCreateTodayWorkout(Student, StudentPlanAssignment)**
```php
$workout = $service->getOrCreateTodayWorkout($student, $assignment);
// Si existe "in_progress", lo retorna
// Si no, crea uno nuevo con exercises_data del día actual
// Status inicial: pending
```

#### 5. **getTotalPlanDays(StudentPlanAssignment)**
```php
$days = $service->getTotalPlanDays($assignment);
// Cuántos días únicos hay en el plan
```

#### 6. **calculateExpectedSessions(StudentPlanAssignment)**
```php
$expected = $service->calculateExpectedSessions($assignment);
// expected = total_dias * (semanas_entre_starts_at y ends_at)
```

#### 7. **calculateProgress(StudentPlanAssignment)**
```php
$progress = $service->calculateProgress($assignment);
// Retorna:
// [
//     'completed_workouts' => int,
//     'expected_sessions' => int,
//     'progress_percentage' => float (puede ser >100),
//     'is_on_track' => bool (>= 100%),
//     'is_bonus' => bool (> 100%),
// ]
```

#### 8. **isCurrentCycleComplete(StudentPlanAssignment)**
```php
$complete = $service->isCurrentCycleComplete($assignment);
// true si completó todos los días del ciclo actual
```

#### 9. **getProgressSummary(Student)**
```php
$summary = $service->getProgressSummary($student);
// Resumen completo: plan activo, ciclo, día siguiente, progreso, etc.
```

---

### `WorkoutNotificationPreferencesService`
Gestiona preferencias de recordatorios del estudiante.

#### Estructura en `student.data['notifications']`
```php
[
    'workout_reminders_enabled' => bool,
    'preferred_days' => ['monday', 'wednesday', 'friday'],
    'preferred_times' => ['08:00', '18:00'],
    'channels' => ['push', 'email'],
    'timezone' => 'America/New_York',
    'reminder_minutes_before' => 30,
    'rest_between_reminders_hours' => 24,
]
```

#### Métodos principales:

```php
// Obtener preferencias
$prefs = $service->getPreferences($student);

// Actualizar
$service->updatePreferences($student, [
    'enabled' => true,
    'preferred_days' => ['monday', 'wednesday', 'friday'],
    'preferred_times' => ['08:00', '18:00'],
    'channels' => ['push'],
]);

// Validaciones
$isPreferredDay = $service->isPreferredDay($student);
$isPreferredTime = $service->isPreferredTime($student);

// Siguiente ventana válida para recordatorio
$nextWindow = $service->getNextReminderWindow($student);

// Verificar si enviar recordatorio ahora
$should = $service->shouldSendReminderNow($student, $lastReminderAt);

// Canales activos
$channels = $service->getActiveChannels($student);
```

---

## 🚀 Workflow Completo (Usuario + Sistema)

### Paso 1: Resolver Plan Activo
```php
$orchestration = new WorkoutOrchestrationService();
$plan = $orchestration->resolveActivePlan($student);

if (!$plan) {
    // Mostrar: "Sin plan activo" + CTA para asignar plan
    return;
}
```

### Paso 2-3: Obtener/Crear Workout del Día
```php
$workout = $orchestration->getOrCreateTodayWorkout($student, $plan);

if (!$workout) {
    // Plan vacío, manejo de error
    return;
}

// Datos iniciales
$planDay = $workout->plan_day;
$exercisesForToday = $workout->exercises_data;
```

### Paso 4: Durante la Sesión (Mobile/Web)
El usuario entrena, la app actualiza ejercicios:
```php
$updatedExercises = [
    [
        'id' => '...', 
        'name' => 'Push-up',
        'completed' => true,
        'sets' => [
            ['reps' => 10, 'weight' => 0, 'completed' => true],
            ['reps' => 10, 'weight' => 0, 'completed' => true],
            ['reps' => 8, 'weight' => 0, 'completed' => true],
        ]
    ],
    // ... más ejercicios
];

$workout->updateExercisesData($updatedExercises);
// Auto-save cada N segundos
```

### Paso 5: Completar Workout
```php
$durationMinutes = 45;
$rating = 4; // 1-5
$notes = "Felt strong today";
$survey = [
    'fatigue' => 3,        // 1-5 escala
    'rpe' => 7,            // RPE (6-20)
    'pain' => 0,           // 1-5
    'mood' => 'good',      // enum
];

$workout->completeWorkout($durationMinutes, $rating, $notes, $survey);
```

### Paso 6: Proponer Actualización de Peso
```php
// En la respuesta de finalización de workout:
// Mostrar modal/form: "¿Actualizar tu peso actual?"

// Si acepta:
$entry = StudentWeightEntry::create([
    'student_id' => $student->id,
    'weight_kg' => 75.5,
    'recorded_at' => now(),
    'source' => 'manual',
]);

// Calcular cambio desde última entrada
$change = StudentWeightEntry::weightChangeSince(
    $student->id,
    now()->subMonth()
);
// Retorna: ['change_kg', 'change_percentage', 'period_days']
```

### Paso 7: Progreso y Recompensas
```php
$progress = $orchestration->calculateProgress($plan);
// progress_percentage puede ser >100 si entrena extra

if ($orchestration->isCurrentCycleComplete($plan)) {
    // ¡Ciclo completado! Emitir badge/log
    // Si repite ciclo dentro del período: "BONUS!"
}
```

### Paso 8: Recordatorios (Queue/Cron)
```php
$notificationService = new WorkoutNotificationPreferencesService();

// En scheduled job (ej: cada 5 minutos)
foreach (Student::all() as $student) {
    if ($notificationService->shouldSendReminderNow($student)) {
        $channels = $notificationService->getActiveChannels($student);
        
        foreach ($channels as $channel) {
            // Enviar recordatorio por canal (push, email, sms)
        }
    }
}
```

---

## 📝 Métodos Útiles de Workout

```php
// Iniciar workout
$workout->startWorkout();  // status = in_progress, started_at = now()

// Completar
$workout->completeWorkout($minutes, $rating, $notes, $survey);

// Saltar
$workout->skip("Injury");

// Actualizar ejercicios
$workout->updateExercisesData($exercisesArray);

// Accessors
$workout->is_completed;    // bool
$workout->is_in_progress;  // bool
$workout->formatted_duration;  // "1h 30m"
$workout->plan_day_label;  // "Day 3"
$workout->exercises;       // array
$workout->getExerciseProgress();  // Array con progreso
```

---

## 📈 Métodos Útiles de StudentWeightEntry

```php
// Crear entrada
StudentWeightEntry::create([...]);

// Obtener última
$latest = StudentWeightEntry::latestForStudent($student->id);

// Cambio desde fecha
$change = StudentWeightEntry::weightChangeSince(
    $student->id,
    now()->subMonth()
);
// ['initial_weight_kg', 'current_weight_kg', 'change_kg', 'change_percentage', 'period_days']

// Promedio en período
$avg = StudentWeightEntry::averageWeightForPeriod(
    $student->id,
    now()->subMonth(),
    now()
);

// Scopos útiles
StudentWeightEntry::forStudent($id)->latest()->take(10)->get();
StudentWeightEntry::lastWeeks(4)->get();  // Últimas 4 semanas
```

---

## 🗂️ Estructura de Archivos

```
app/Models/Tenant/
├── Workout.php
├── StudentWeightEntry.php
├── StudentPlanAssignment.php (actualizado: +workouts relation)
└── Student.php (actualizado: +workouts, +weightEntries)

app/Services/
├── WorkoutOrchestrationService.php
└── WorkoutNotificationPreferencesService.php

app/Enums/
└── WorkoutStatus.php  (pending|in_progress|completed|skipped)

database/migrations/tenant/
├── 2026_01_11_000001_create_workouts_table.php
└── 2026_01_11_000002_create_student_weight_entries_table.php
```

---

## ⚡ Próximos Pasos

- [ ] Migrations: `php artisan migrate:tenant`
- [ ] Seeders (test data)
- [ ] Controladores API: `/api/workouts`, `/api/weight-entries`
- [ ] Livewire components para dashboard + workout execution
- [ ] Queue jobs para recordatorios (cron: cada 5min)
- [ ] Eventos/listeners para badges y celebraciones
- [ ] Documentación de API (OpenAPI/Swagger)

---

**Last Updated:** 2026-01-11  
**Status:** ✅ Migrations & Models Ready
