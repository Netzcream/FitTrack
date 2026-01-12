<div class="space-y-6 md:space-y-8">

    {{-- ENCABEZADO --}}
    <x-student-header
        title="Panel de entrenamiento"
        subtitle="Resumen de tu actividad y tus próximos pasos"
        icon="dumbbell"
        :student="$student" />

    {{-- ALERTAS (ahora arriba del contenido principal) --}}
    @if ($goalThisMonth && $trainingsThisMonth >= $goalThisMonth)
        <div class="rounded p-4 border-l-4 flex items-start gap-3"
            style="border-color: var(--ftt-color-base);
                    background-color: var(--ftt-color-base-transparent);">
            <x-icons.lucide.star class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: var(--ftt-color-base)" />
            <p class="text-sm font-medium text-gray-800">
                ¡Excelente! Completaste tu meta mensual
            </p>
        </div>
    @endif

    @if ($hasPendingPayment)
        <div class="border-l-4 p-4 rounded bg-red-50 border-red-500 flex items-start gap-3">
            <x-icons.lucide.alert-circle class="w-5 h-5 flex-shrink-0 text-red-500 mt-0.5" />
            <div>
                <p class="text-sm text-red-700">
                    Tenés un pago pendiente.
                </p>
                <a href="{{ route('tenant.student.payments') }}" class="text-sm text-red-600 underline hover:text-red-700">Ver pagos</a>
            </div>
        </div>
    @endif

    @if (!$assignment)
        <div class="border-l-4 p-4 rounded bg-gray-50 border-gray-400 flex items-start gap-3">
            <x-icons.lucide.alert-circle class="w-5 h-5 flex-shrink-0 text-gray-500 mt-0.5" />
            <p class="text-sm text-gray-700">
                {{ $noActivePlanMessage ?? 'No tenés un plan activo. Contactá a tu entrenador.' }}
            </p>
        </div>
    @endif

    {{-- WORKOUT DE HOY + PROGRESO --}}
    @if ($assignment && $todayWorkout)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Columna izquierda: Workout de hoy --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold"
                                style="background-color: var(--ftt-color-base); color: white;">
                                Día {{ $todayWorkout->plan_day }}
                            </span>
                            <h3 class="text-lg font-semibold text-gray-900">Entrenamiento de Hoy</h3>
                        </div>
                        <span class="text-sm text-gray-500">
                            @if ($todayWorkout->is_in_progress)
                                <span class="text-blue-600 font-medium">En progreso</span>
                                @php
                                    $elapsedMinutes = data_get($todayWorkout->meta, 'live_elapsed_minutes', 0);
                                    $hours = intdiv($elapsedMinutes, 60);
                                    $mins = $elapsedMinutes % 60;
                                @endphp
                                @if ($elapsedMinutes > 0)
                                    <span class="text-gray-600 ml-2">
                                        @if ($hours > 0)
                                            {{ $hours }}h {{ $mins }}m
                                        @else
                                            {{ $mins }}m
                                        @endif
                                    </span>
                                @endif
                            @elseif ($todayWorkout->status === 'completed')
                                <span class="text-green-600 font-medium">✓ Completado</span>
                            @elseif ($todayWorkout->status === 'skipped')
                                <span class="text-yellow-600 font-medium">Saltado</span>
                            @else
                                <span class="text-gray-500">Pendiente</span>
                            @endif
                        </span>
                    </div>

                    {{-- Ejercicios del día --}}
                    @if ($todayWorkout->exercises_data && count($todayWorkout->exercises_data) > 0)
                        <div class="space-y-3">
                            @foreach ($todayWorkout->exercises_data as $exercise)
                                <div class="border border-gray-100 rounded-lg p-4 flex items-start justify-between hover:bg-gray-50 transition">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">{{ $exercise['name'] ?? 'Ejercicio sin nombre' }}</h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            @if (isset($exercise['sets']))
                                                {{ count($exercise['sets']) }} serie(s)
                                                @if (isset($exercise['sets'][0]['reps']))
                                                    × {{ $exercise['sets'][0]['reps'] }} reps
                                                @endif
                                            @else
                                                Sin detalles
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        @if ($exercise['completed'] ?? false)
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full" style="background-color: var(--ftt-color-base); color: white;">
                                                <x-icons.lucide.check class="w-4 h-4" />
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-500">
                                                <x-icons.lucide.circle class="w-4 h-4" />
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button wire:click="startOrContinueWorkout" class="start-button w-full mt-6 flex items-center gap-2 justify-center">
                            @if ($todayWorkout->is_in_progress)
                                <x-icons.lucide.rotate-cw class="w-4 h-4" />
                                Continuar entrenamiento
                            @else
                                <x-icons.lucide.zap class="w-4 h-4" />
                                Comenzar entrenamiento
                            @endif
                        </button>
                    @else
                        <p class="text-center py-6 text-gray-500">No hay ejercicios para hoy</p>
                    @endif
                </div>
            </div>

            {{-- Columna derecha: Progreso --}}
            <div class="space-y-4">
                {{-- Card de Progreso --}}
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Progreso del Plan</h3>

                    @if (!empty($progressData))
                        <div class="space-y-4">
                            <div>
                                <div class="flex items-end justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-600">Completadas</span>
                                    <span class="text-2xl font-bold" style="color: var(--ftt-color-base);">
                                        {{ $progressData['completed_workouts'] ?? 0 }}
                                    </span>
                                </div>
                                <div class="bg-gray-100 h-2 rounded-full overflow-hidden">
                                    <div class="h-2 rounded-full transition-all duration-500"
                                        style="width: {{ min(100, ($progressData['progress_percentage'] ?? 0)) }}%;
                                                background-color: var(--ftt-color-base)">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    {{ round($progressData['progress_percentage'] ?? 0, 1) }}% de {{ $progressData['expected_sessions'] ?? 0 }} esperadas
                                </p>
                            </div>

                            <div class="pt-3 border-t border-gray-200">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-xs text-gray-500">Ciclo actual</p>
                                        <p class="text-xl font-bold text-gray-900">1</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Próximo día</p>
                                        <p class="text-xl font-bold" style="color: var(--ftt-color-base);">
                                            {{ $todayWorkout->plan_day ?? '—' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-4">Sin datos de progreso</p>
                    @endif
                </div>

                {{-- Card de Plan Actual --}}
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Plan Activo</h3>
                    <p class="font-semibold text-gray-900 truncate">{{ $assignment->name }}</p>
                    <p class="text-xs text-gray-500 mt-2">
                        Hasta {{ $assignment->ends_at?->format('d/m/Y') ?? '—' }}
                    </p>
                    <a href="{{ route('tenant.student.plan-detail', $assignment->uuid) }}"
                        class="inline-block mt-3 text-sm font-medium underline"
                        style="color: var(--ftt-color-base);">
                        Ver detalles →
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- PROGRESO MENSUAL (Fallback si no hay workout) --}}
    @if ($assignment && !$todayWorkout)
        <div
            class="bg-white rounded-xl shadow-md p-6 flex flex-col md:flex-row justify-between items-center gap-4 border border-gray-200">
            <div>
                <p class="text-sm text-gray-500">Entrenamientos este mes</p>
                <h2 class="text-3xl font-bold" style="color: var(--ftt-color-base)">
                    {{ $trainingsThisMonth }}
                </h2>
                <p class="text-xs text-gray-400">Meta: {{ $goalThisMonth }}</p>

                <div class="w-56 bg-gray-100 h-2 rounded-full mt-2 overflow-hidden">
                    <div class="h-2 rounded-full transition-all duration-500"
                        style="width: {{ min(100, ($trainingsThisMonth / max(1, $goalThisMonth)) * 100) }}%;
                                background-color: var(--ftt-color-base)">
                    </div>
                </div>
            </div>

            <button wire:click="startOrContinueWorkout" class="start-button flex items-center gap-2 justify-center">
                <x-icons.lucide.zap class="w-4 h-4" />
                Comenzar entrenamiento
            </button>
        </div>
    @endif

    {{-- RUTINA Y PLANES (mostrar plan si existe) --}}
    @if ($assignment)
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <x-icons.lucide.dumbbell class="w-5 h-5" />
                    <span>Rutina de entrenamiento</span>
                </div>
                <p class="text-xl font-semibold text-gray-900">{{ $assignment->name }}</p>
                <p class="text-sm text-gray-500">
                    Vigente desde {{ $assignment->starts_at?->format('d/m/Y') ?? '—' }}
                    @if ($assignment->ends_at)
                        · hasta {{ $assignment->ends_at->format('d/m/Y') }}
                    @endif
                </p>
                <div class="flex flex-wrap gap-3 text-sm text-gray-600">
                    <span>{{ $assignment->exercises_by_day->count() }} días</span>
                    <span>{{ $assignment->exercises_by_day->flatten(1)->count() }} ejercicios</span>
                    <span>Objetivo: {{ $assignment->plan?->goal ?? '—' }}</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                <a href="{{ route('tenant.student.download-plan', $assignment->uuid) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white shadow-sm transition"
                    style="background-color: var(--ftt-color-base);">
                    <x-icons.lucide.file-down class="w-4 h-4" />
                    Descargar PDF
                </a>
                <a href="{{ route('tenant.student.plan-detail', $assignment->uuid) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 bg-white text-gray-700 shadow-sm transition">
                    <x-icons.lucide.list class="w-4 h-4" />
                    Ver detalle
                </a>
            </div>
        </div>
    @endif

    {{-- ACCESOS RÁPIDOS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
        <a href="{{ $assignment ? route('tenant.student.plan-detail', $assignment->uuid) : '#' }}" class="student-card">
            <x-icons.lucide.dumbbell class="w-7 h-7 mb-2" style="color: var(--ftt-color-base)" />
            <h3>Mi rutina</h3>
            <p>Ver ejercicios</p>
        </a>

        <a href="{{ route('tenant.student.progress') }}" class="student-card">
            <x-icons.lucide.line-chart class="w-7 h-7 mb-2" style="color: var(--ftt-color-base)" />
            <h3>Progreso</h3>
            <p>Ver métricas</p>
        </a>

        <a href="{{ route('tenant.student.messages') }}" class="student-card">
            <x-icons.lucide.message-circle class="w-7 h-7 mb-2" style="color: var(--ftt-color-base)" />
            <h3>Mensajes</h3>
            <p>Hablar con tu entrenador</p>
        </a>
    </div>
</div>
