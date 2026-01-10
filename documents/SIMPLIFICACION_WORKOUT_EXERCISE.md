# 🎯 Simplificación de Workout y Exercise

## Fecha: 2026-01-06

## ❌ Problema Anterior

El sistema tenía **3 tablas** y **3 modelos** para manejar entrenamientos:

1. `exercises` - Catálogo de ejercicios
2. `workout_exercises` - Tabla pivot con datos de ejecución 
3. `workouts` - Sesiones de entrenamiento

### Problemas:
- ❌ Clonar un workout requería múltiples INSERT y queries complejas
- ❌ Leer un workout requería 2-3 JOINs
- ❌ Código complejo y difícil de mantener
- ❌ 3 modelos interrelacionados

---

## ✅ Solución Implementada

### Estructura Simplificada: 2 Modelos

1. **`Exercise`** - Catálogo básico (sin cambios)
   - Solo información del ejercicio
   - Usado como referencia

2. **`Workout`** - Modelo unificado
   - Almacena todo en un campo JSON: `exercises_data`
   - Incluye toda la información de los ejercicios realizados
   - Histórico preservado

### Schema Final

```sql
workouts
  - id
  - uuid
  - student_id
  - training_plan_id
  - date
  - duration_minutes
  - status
  - notes
  - rating
  - exercises_data (JSON) ← 🔥 TODA LA DATA AQUÍ
  - meta (JSON)
  - timestamps
  - soft_deletes
```

### Formato de `exercises_data`

```json
[
  {
    "exercise_id": 5,
    "exercise_name": "Press Banca",
    "sets_completed": 4,
    "reps_per_set": [10, 10, 8, 8],
    "weight_used_kg": 80,
    "duration_seconds": null,
    "rest_time_seconds": 90,
    "notes": "Buena técnica",
    "completed_at": "2026-01-06 14:30:00",
    "order": 1
  },
  {
    "exercise_id": 12,
    "exercise_name": "Sentadillas",
    "sets_completed": 3,
    "reps_per_set": [12, 10, 10],
    "weight_used_kg": 100,
    "duration_seconds": null,
    "rest_time_seconds": 60,
    "notes": null,
    "completed_at": "2026-01-06 14:45:00",
    "order": 2
  }
]
```

---

## 🚀 Ventajas

| Acción | Antes | Después |
|--------|-------|---------|
| **Crear workout** | 1 INSERT + N INSERTS | 1 INSERT |
| **Clonar workout** | 1 INSERT + N INSERTS + queries | `$workout->clone()` |
| **Ver workout** | 2-3 JOINs | 0 JOINs |
| **Tablas** | 3 | 2 |
| **Modelos** | 3 | 2 |

### Beneficios Específicos:

1. ✅ **Clonar = `$workout->replicate()->save()`** - instantáneo
2. ✅ **Sin joins** - todo en una query
3. ✅ **Flexible** - agregás campos sin migraciones
4. ✅ **Simple** - un solo modelo para workouts
5. ✅ **Histórico preservado** - si borrás un Exercise del catálogo, el workout guarda el nombre

---

## 📝 Cambios en el Código

### Modelo `Workout.php`

```php
// NUEVO: Campo exercises_data en fillable y casts
protected $fillable = [
    'uuid',
    'student_id',
    'training_plan_id',
    'date',
    'duration_minutes',
    'status',
    'notes',
    'rating',
    'exercises_data', // 🔥 NUEVO
    'meta',
];

protected $casts = [
    'date'             => 'date',
    'duration_minutes' => 'integer',
    'rating'           => 'integer',
    'exercises_data'   => 'array', // 🔥 NUEVO - Cast automático a array
    'meta'             => 'array',
];

// NUEVO: Métodos helper
public function addExercise(array $exerciseData): void
{
    $exercises = $this->exercises_data ?? [];
    $exercises[] = array_merge($exerciseData, ['order' => count($exercises) + 1]);
    $this->exercises_data = $exercises;
    $this->save();
}

public function clone(array $attributes = []): self
{
    $clone = $this->replicate();
    foreach ($attributes as $key => $value) {
        $clone->{$key} = $value;
    }
    $clone->save();
    return $clone;
}

public function getExercisesAttribute()
{
    return collect($this->exercises_data ?? []);
}
```

### API Controller

```php
// Crear workout con ejercicios en JSON
$exercisesData = [];
foreach ($request->exercises as $idx => $exerciseData) {
    $exercise = Exercise::find($exerciseData['exercise_id']);
    
    $exercisesData[] = [
        'exercise_id'        => $exerciseData['exercise_id'],
        'exercise_name'      => $exercise->name ?? 'N/A',
        'sets_completed'     => $exerciseData['sets_completed'] ?? null,
        'reps_per_set'       => $exerciseData['reps_per_set'] ?? [],
        'weight_used_kg'     => $exerciseData['weight_used_kg'] ?? null,
        'duration_seconds'   => $exerciseData['duration_seconds'] ?? null,
        'rest_time_seconds'  => $exerciseData['rest_time_seconds'] ?? null,
        'notes'              => $exerciseData['notes'] ?? null,
        'completed_at'       => now()->format('Y-m-d H:i:s'),
        'order'              => $idx + 1,
    ];
}

$workout = Workout::create([
    'student_id'       => $student->id,
    'training_plan_id' => $request->training_plan_id,
    'date'             => $request->date,
    'duration_minutes' => $request->duration_minutes,
    'status'           => $request->status ?? 'completed',
    'notes'            => $request->notes,
    'rating'           => $request->rating,
    'exercises_data'   => $exercisesData, // 🔥 Todo en JSON
]);
```

---

## 📦 Migraciones Creadas

### 1. `2026_01_06_000001_add_exercises_data_to_workouts_table.php`
- Agrega el campo `exercises_data` (JSON) a `workouts`

### 2. `2026_01_06_000002_drop_workout_exercises_table.php`
- Migra datos existentes de `workout_exercises` a `exercises_data`
- Elimina la tabla `workout_exercises`
- Incluye rollback completo

---

## 🗂️ Archivos Modificados

1. ✅ `app/Models/Tenant/Workout.php` - Actualizado con exercises_data
2. ✅ `app/Models/Tenant/WorkoutExercise.php` - Vaciado (deprecado)
3. ✅ `app/Http/Controllers/Api/WorkoutApiController.php` - Actualizado
4. ✅ Migraciones creadas

---

## 📋 Pasos para Aplicar

```bash
# 1. Ejecutar migraciones (en orden)
php artisan tenants:run migrate

# 2. Verificar que la data migró correctamente
php artisan tinker
>>> Workout::first()->exercises_data

# 3. Listo para usar!
```

---

## 🔄 Cómo Clonar un Workout (Ejemplo)

```php
// ✅ NUEVO: Super simple
$newWorkout = $workout->clone([
    'date' => today(),
    'student_id' => $otherStudent->id,
]);

// ❌ ANTES: Complejo
$newWorkout = $workout->replicate();
$newWorkout->save();
foreach ($workout->exercises as $exercise) {
    WorkoutExercise::create([
        'workout_id' => $newWorkout->id,
        'exercise_id' => $exercise->exercise_id,
        // ... copiar todos los campos
    ]);
}
```

---

## ⚠️ Notas Importantes

1. La tabla `plan_exercise` se **MANTIENE** - es necesaria para los planes de entrenamiento (plantillas)
2. El modelo `Exercise` se **MANTIENE** - es el catálogo de ejercicios
3. `WorkoutExercise.php` está vacío pero no borrado (por compatibilidad temporal)
4. Las migraciones incluyen rollback completo

---

## 🎉 Resultado

**Antes:** 3 tablas, 3 modelos, código complejo
**Después:** 2 tablas, 2 modelos, código simple y claro

¡Sistema simplificado y más mantenible! 🚀
