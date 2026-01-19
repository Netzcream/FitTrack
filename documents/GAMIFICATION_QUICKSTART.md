# Quick Start: Integración de Gamificación

## 🚀 Configuración inicial (una sola vez)

### 1. Ejecutar migraciones

```bash
php artisan tenants:migrate
```

Esto creará las tablas:
- `student_gamification_profiles`
- `exercise_completion_logs`

### 2. Verificar que el evento está registrado

El sistema ya está configurado en `EventServiceProvider.php`:

```php
\App\Events\Tenant\ExerciseCompleted::class => [
    \App\Listeners\Tenant\AwardExperiencePoints::class,
],
```

✅ Listo. No necesitas hacer nada más.

---

## 📝 Uso básico

### Disparar evento cuando se completa un ejercicio

En tu controller, Livewire component o servicio donde manejas completados de ejercicios:

```php
use App\Events\Tenant\ExerciseCompleted;

// Cuando el alumno completa un ejercicio
event(new ExerciseCompleted(
    student: $student,      // Modelo Student
    exercise: $exercise,    // Modelo Exercise
    workout: $workout,      // Modelo Workout (opcional)
    completedAt: now()      // Carbon (opcional)
));
```

**¡Eso es todo!** El listener se encarga del resto automáticamente.

---

## 🎯 Mostrar stats en vista

### Obtener estadísticas del alumno

```php
use App\Services\Tenant\GamificationService;

// En controller o Livewire component
$service = new GamificationService();
$stats = $service->getStudentStats($student);

// Pasar a vista
return view('student.dashboard', compact('stats'));
```

### En la vista Blade

```blade
@if($stats['has_profile'])
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-bold mb-4">
            {{ __('gamification.my_progress') }}
        </h3>
        
        {{-- Badge y nivel --}}
        <div class="flex items-center gap-4 mb-6">
            <span class="px-4 py-2 rounded-full bg-blue-500 text-white font-semibold">
                {{ $stats['tier_name'] }}
            </span>
            <div>
                <p class="text-2xl font-bold">{{ __('gamification.level') }} {{ $stats['current_level'] }}</p>
                <p class="text-sm text-gray-600">{{ $stats['total_xp'] }} XP</p>
            </div>
        </div>
        
        {{-- Barra de progreso --}}
        <div class="mb-4">
            <div class="flex justify-between text-sm mb-2">
                <span>{{ __('gamification.level_progress') }}</span>
                <span>{{ $stats['level_progress'] }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-blue-500 h-3 rounded-full transition-all" 
                     style="width: {{ $stats['level_progress'] }}%">
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1">
                {{ $stats['xp_for_next_level'] - $stats['total_xp'] }} XP {{ __('gamification.xp_to_next_level') }}
            </p>
        </div>
        
        {{-- Estadísticas --}}
        <div class="text-sm text-gray-600">
            <p>{{ $stats['total_exercises'] }} {{ __('gamification.exercises_completed') }}</p>
            @if($stats['last_completed'])
                <p>{{ __('gamification.last_exercise') }}: {{ $stats['last_completed'] }}</p>
            @endif
        </div>
    </div>
@else
    <div class="bg-gray-50 p-6 rounded-lg text-center">
        <p class="text-gray-600">{{ __('gamification.no_activity_yet') }}</p>
        <p class="text-sm text-gray-500 mt-2">{{ __('gamification.start_training') }}</p>
    </div>
@endif
```

---

## 🔥 Ejemplo completo: Livewire Component

```php
<?php

namespace App\Livewire\Tenant\Workouts;

use Livewire\Component;
use App\Models\Tenant\Workout;
use App\Models\Tenant\Exercise;
use App\Events\Tenant\ExerciseCompleted;
use App\Services\Tenant\GamificationService;

class WorkoutSession extends Component
{
    public Workout $workout;
    public array $completedExercises = [];
    
    public function mount(Workout $workout)
    {
        $this->workout = $workout;
    }
    
    public function markExerciseCompleted($exerciseId)
    {
        $exercise = Exercise::findOrFail($exerciseId);
        $student = auth()->user()->student;
        
        // Tu lógica de negocio para marcar como completado
        // ...
        
        // Agregar a array local
        $this->completedExercises[] = $exerciseId;
        
        // Disparar evento de gamificación
        event(new ExerciseCompleted(
            student: $student,
            exercise: $exercise,
            workout: $this->workout
        ));
        
        // Feedback visual
        $this->dispatch('exercise-completed', [
            'exerciseName' => $exercise->name,
            'xpEarned' => $this->getXpForLevel($exercise->level),
        ]);
    }
    
    private function getXpForLevel($level): int
    {
        return match($level) {
            'beginner' => 10,
            'intermediate' => 15,
            'advanced' => 20,
            default => 10,
        };
    }
    
    public function render()
    {
        $gamificationService = new GamificationService();
        $stats = $gamificationService->getStudentStats(auth()->user()->student);
        
        return view('livewire.tenant.workouts.workout-session', [
            'stats' => $stats,
        ]);
    }
}
```

### Vista del componente

```blade
<div>
    {{-- Mini widget de gamificación --}}
    @if($stats['has_profile'])
        <div class="mb-4 p-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">{{ $stats['tier_name'] }}</p>
                    <p class="font-bold">Nivel {{ $stats['current_level'] }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold">{{ $stats['total_xp'] }}</p>
                    <p class="text-xs opacity-90">XP</p>
                </div>
            </div>
        </div>
    @endif
    
    {{-- Lista de ejercicios --}}
    <div class="space-y-4">
        @foreach($workout->exercises_data as $index => $exerciseData)
            <div class="p-4 border rounded-lg">
                <h4 class="font-semibold">{{ $exerciseData['name'] }}</h4>
                <p class="text-sm text-gray-600">{{ $exerciseData['level'] }}</p>
                
                @if(in_array($exerciseData['id'], $completedExercises))
                    <span class="text-green-500">✅ Completado</span>
                @else
                    <button wire:click="markExerciseCompleted({{ $exerciseData['id'] }})"
                            class="mt-2 px-4 py-2 bg-blue-500 text-white rounded">
                        Marcar como completado
                    </button>
                @endif
            </div>
        @endforeach
    </div>
</div>

@script
<script>
    $wire.on('exercise-completed', (event) => {
        // Mostrar toast/notificación
        alert(`¡${event.exerciseName} completado! +${event.xpEarned} XP`);
    });
</script>
@endscript
```

---

## 🧪 Testing rápido

### Crear perfil manual para un alumno

```bash
php artisan tinker
```

```php
$student = App\Models\Tenant\Student::first();
$service = new App\Services\Tenant\GamificationService();

// Crear perfil
$profile = $service->getOrCreateProfile($student);
dd($profile);
```

### Simular completado de ejercicio

```php
$student = App\Models\Tenant\Student::first();
$exercise = App\Models\Tenant\Exercise::first();

event(new App\Events\Tenant\ExerciseCompleted($student, $exercise));

// Procesar queue
php artisan queue:work --once

// Ver resultado
$service = new App\Services\Tenant\GamificationService();
$stats = $service->getStudentStats($student);
dd($stats);
```

### Ver tabla de niveles

```php
$service = new App\Services\Tenant\GamificationService();
$table = $service->getLevelTable(20);

foreach ($table as $row) {
    echo "Nivel {$row['level']}: {$row['xp_required']} XP (Tier {$row['tier']}, Badge: {$row['badge']})\n";
}
```

---

## 🎨 Ejemplos de badges con Tailwind

```blade
{{-- Badge según tier --}}
@php
    $badgeClasses = match($stats['current_tier']) {
        0 => 'bg-gray-200 text-gray-800',
        1 => 'bg-green-200 text-green-800',
        2 => 'bg-blue-200 text-blue-800',
        3 => 'bg-yellow-200 text-yellow-800',
        4 => 'bg-purple-200 text-purple-800',
        5 => 'bg-red-200 text-red-800',
        default => 'bg-gray-200 text-gray-800',
    };
@endphp

<span class="px-3 py-1 rounded-full text-sm font-semibold {{ $badgeClasses }}">
    {{ $stats['tier_name'] }}
</span>
```

---

## 📊 API para móvil (futuro)

Si necesitas exponer gamificación en API:

```php
// routes/api.php

Route::middleware(['auth:sanctum', ApiTenancy::class])->group(function () {
    Route::get('/gamification/stats', function () {
        $service = new GamificationService();
        $student = auth()->user()->student;
        return response()->json($service->getStudentStats($student));
    });
    
    Route::get('/gamification/history', function () {
        $service = new GamificationService();
        $student = auth()->user()->student;
        return response()->json($service->getRecentCompletions($student, 20));
    });
});
```

---

## ⚙️ Personalización

### Cambiar XP por dificultad

Editar `config/gamification.php`:

```php
'xp' => [
    'beginner' => 15,        // cambiar de 10 a 15
    'intermediate' => 25,    // cambiar de 15 a 25
    'advanced' => 35,        // cambiar de 20 a 35
],
```

### Cambiar progresión de niveles

```php
'level_progression' => [
    'base_xp' => 150,        // cambiar de 100 a 150
    'growth_factor' => 1.2,  // cambiar de 1.15 a 1.2 (más difícil)
    'round_to' => 10,
],
```

---

## 🔍 Debug

### Ver logs de gamificación

```bash
tail -f storage/logs/laravel.log | grep -i "nivel\|tier\|xp\|gamif"
```

### Verificar si un ejercicio ya fue completado

```php
use App\Models\Tenant\ExerciseCompletionLog;

$wasCompleted = ExerciseCompletionLog::wasExerciseCompletedToday(
    $studentId, 
    $exerciseId, 
    now()
);

dd($wasCompleted);
```

---

## ✅ Checklist de integración

- [ ] Ejecutar `php artisan tenants:migrate`
- [ ] Disparar evento `ExerciseCompleted` cuando se completa ejercicio
- [ ] Mostrar widget de gamificación en dashboard del alumno
- [ ] (Opcional) Agregar notificación visual al ganar XP
- [ ] (Opcional) Agregar página de historial de completados
- [ ] (Opcional) Exponer endpoints en API móvil

---

**¿Dudas?** Consulta [GAMIFICATION_SYSTEM.md](./GAMIFICATION_SYSTEM.md) para documentación completa.
