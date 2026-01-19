{{--
    EJEMPLO DE VISTA: Livewire Component

    Vista correspondiente al ejemplo WorkoutSessionExample.php

    NOTA: Este archivo es solo de ejemplo. Adaptá este código a tu diseño específico.
--}}

<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">

        {{-- Header --}}
        <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95 pb-4">
            <div class="flex items-center justify-between gap-4 max-w-3xl">
                <div>
                    <flux:heading size="xl" level="1">Sesión de entrenamiento</flux:heading>
                    <flux:subheading size="lg">{{ $workout->plan_day ? "Día {$workout->plan_day}" : 'Workout libre' }}</flux:subheading>
                </div>

                @if(!$workoutStarted)
                    <flux:button wire:click="startWorkout" variant="primary" icon="play">
                        Iniciar entrenamiento
                    </flux:button>
                @elseif($workoutCompleted)
                    <flux:button as="a" href="{{ route('tenant.dashboard.workouts.index') }}" variant="filled" icon="check-circle">
                        Ver historial
                    </flux:button>
                @endif
            </div>
            <flux:separator variant="subtle" class="mt-2" />
        </div>

        <div class="max-w-3xl space-y-6">

            {{-- 🎮 Widget de Gamificación (siempre visible) --}}
            @if($workoutStarted)
                <x-gamification-widget
                    size="compact"
                    :show-progress="false"
                    class="animate-pulse-subtle"
                />
            @endif

            {{-- Estado del workout --}}
            @if($workoutCompleted)
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-3xl">🎉</span>
                        <div>
                            <h3 class="text-lg font-bold text-green-900 dark:text-green-100">
                                ¡Entrenamiento completado!
                            </h3>
                            <p class="text-sm text-green-700 dark:text-green-300">
                                Has completado {{ count($completedExercises) }} ejercicios
                            </p>
                        </div>
                    </div>

                    {{-- Resumen de XP ganado --}}
                    @if($gamificationStats['has_profile'])
                        <div class="bg-white dark:bg-neutral-800 rounded-lg p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">XP ganado en esta sesión</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                +{{ count($completedExercises) * 15 }} XP aprox.
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Lista de ejercicios --}}
            <div class="space-y-4">
                @foreach($exercises as $index => $exercise)
                    @php
                        $isCompleted = in_array($exercise['id'], $completedExercises);
                        $xpValue = match($exercise['level'] ?? 'beginner') {
                            'beginner', 'principiante' => 10,
                            'intermediate', 'intermedio' => 15,
                            'advanced', 'avanzado' => 20,
                            default => 10,
                        };
                    @endphp

                    <div class="border border-gray-200 dark:border-neutral-700 rounded-lg p-4 transition-all {{ $isCompleted ? 'bg-green-50 dark:bg-green-900/10' : 'bg-white dark:bg-neutral-800' }}"
                         wire:key="exercise-{{ $exercise['id'] }}">

                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                {{-- Encabezado del ejercicio --}}
                                <div class="flex items-center gap-3 mb-2">
                                    @if($isCompleted)
                                        <span class="text-2xl">✅</span>
                                    @else
                                        <span class="w-8 h-8 rounded-full bg-gray-200 dark:bg-neutral-700 flex items-center justify-center text-sm font-semibold">
                                            {{ $index + 1 }}
                                        </span>
                                    @endif

                                    <div>
                                        <h4 class="font-semibold text-gray-900 dark:text-white">
                                            {{ $exercise['name'] }}
                                        </h4>

                                        {{-- Badge de dificultad con XP --}}
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs px-2 py-0.5 rounded-full
                                                {{ ($exercise['level'] ?? 'beginner') === 'advanced' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                                {{ ($exercise['level'] ?? 'beginner') === 'intermediate' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                                                {{ ($exercise['level'] ?? 'beginner') === 'beginner' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}">
                                                {{ ucfirst($exercise['level'] ?? 'beginner') }}
                                            </span>

                                            {{-- 🎮 Indicador de XP --}}
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 font-semibold">
                                                +{{ $xpValue }} XP
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Detalles del ejercicio --}}
                                @if(!empty($exercise['sets']) || !empty($exercise['reps']))
                                    <div class="text-sm text-gray-600 dark:text-gray-400 ml-11">
                                        @if(!empty($exercise['sets']))
                                            <span>{{ $exercise['sets'] }} series</span>
                                        @endif
                                        @if(!empty($exercise['reps']))
                                            <span>× {{ $exercise['reps'] }} reps</span>
                                        @endif
                                    </div>
                                @endif

                                @if(!empty($exercise['notes']))
                                    <p class="text-sm text-gray-600 dark:text-gray-400 ml-11 mt-1">
                                        {{ $exercise['notes'] }}
                                    </p>
                                @endif
                            </div>

                            {{-- Botón de acción --}}
                            <div>
                                @if($workoutStarted && !$workoutCompleted)
                                    @if($isCompleted)
                                        <span class="text-green-600 dark:text-green-400 text-sm font-semibold">
                                            Completado
                                        </span>
                                    @else
                                        <flux:button
                                            wire:click="markExerciseCompleted({{ $exercise['id'] }}, {})"
                                            variant="primary"
                                            size="sm">
                                            Completar
                                        </flux:button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Widget de gamificación expandido al final --}}
            @if($workoutCompleted && $gamificationStats['has_profile'])
                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-4">Tu progreso general</h3>
                    <x-gamification-widget
                        size="large"
                        :show-progress="true"
                        :show-stats="true"
                    />
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Scripts para feedback visual --}}
@script
<script>
    // Animación al completar ejercicio
    $wire.on('exercise-completed', (event) => {
        // Mostrar notificación con XP ganado
        if (window.notyf) {
            window.notyf.success({
                message: `${event.exerciseName} completado! +${event.xpEarned} XP`,
                duration: 3000,
            });
        }

        // Confetti opcional si completó todos
        if (event.allCompleted) {
            if (window.confetti) {
                confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: { y: 0.6 }
                });
            }
        }
    });
</script>
@endscript
