<div class="space-y-6">

    @php /** @var \App\Models\User|null $user */ $user = \Illuminate\Support\Facades\Auth::user(); @endphp

    {{-- ALERTA DE PAGO PENDIENTE (arriba de todo) --}}

    {{-- ENCABEZADO --}}
    <x-student-header
        title="Panel de entrenamiento"
        subtitle="Resumen de tu actividad y tus próximos pasos"
        icon="dumbbell"
        :student="$student" />

    {{-- ALERTA DE PAGO PENDIENTE (debajo del título) --}}
    @if ($hasPendingPayment)
        @php
            $invoiceService = new \App\Services\Tenant\InvoiceService();
            $pendingInvoice = $invoiceService->getNextPendingForStudent($user?->student);
            $daysUntilDue = now()->diffInDays($pendingInvoice?->due_date);
            $showAlert = $pendingInvoice && ($pendingInvoice->is_overdue || $daysUntilDue < 5);
        @endphp
        @if ($showAlert)
            <x-student.alert-notification type="warning" :action="['label' => 'Ver pagos', 'url' => route('tenant.student.payments')]">
                <p class="text-sm"><span class="font-medium">Pago {{ $pendingInvoice->is_overdue ? 'Vencido' : 'Pendiente' }}:</span> {{ $pendingInvoice->formatted_amount }} - Vencimiento: {{ $pendingInvoice->due_date->format('d/m/Y') }}</p>
            </x-student.alert-notification>
        @endif
    @endif

    {{-- HERO GAMIFICADO --}}
    @if ($student->gamificationProfile)
        @php
            $profile = $student->gamificationProfile;
            $badgeClass = gamification_badge_class($profile->current_tier);
            $tierIcon = gamification_tier_icon($profile->current_tier);
        @endphp
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden relative">
            <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(128, 90, 213, 0.08), rgba(236, 72, 153, 0.06));"></div>
            <div class="relative p-6 md:p-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                            @if ($tierIcon)
                                <span>{{ $tierIcon }}</span>
                            @endif
                            <span>{{ $profile->tier_name }}</span>
                        </span>
                        <span class="text-sm font-medium text-gray-600">Nivel {{ $profile->current_level }}</span>
                    </div>
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Tu progreso de hoy</h2>
                        <p class="text-sm text-gray-600">Sigue sumando XP para subir de nivel</p>
                    </div>
                    <div class="max-w-2xl">
                        <x-gamification-level-bar :student="$student" />
                    </div>
                    <div class="flex flex-wrap gap-3 text-sm text-gray-700">
                        <div class="px-3 py-2 rounded-lg bg-white/80 border border-gray-200 shadow-sm">
                            <span class="font-semibold">{{ $profile->total_xp }} XP</span> acumulados
                        </div>
                        <div class="px-3 py-2 rounded-lg bg-white/80 border border-gray-200 shadow-sm">
                            <span class="font-semibold">{{ $profile->xp_for_next_level - $profile->total_xp }} XP</span> para el próximo nivel
                        </div>
                        <div class="px-3 py-2 rounded-lg bg-white/80 border border-gray-200 shadow-sm">
                            <span class="font-semibold">{{ $profile->level_progress_percent }}%</span> del nivel actual
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-3 w-full md:w-auto">
                    <button type="button" wire:click="startOrContinueWorkout" class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-white font-semibold shadow-md" style="background-color: var(--ftt-color-base);">
                        <x-icons.lucide.zap class="w-5 h-5" />
                        Continuar entrenamiento
                    </button>
                    <a href="{{ route('tenant.student.progress') }}" class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-gray-300 bg-white text-gray-800 font-semibold shadow-sm hover:bg-gray-50">
                        <x-icons.lucide.line-chart class="w-5 h-5" />
                        Ver mi progreso
                    </a>
                </div>
            </div>
        </div>
    @endif

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



    @if (!$assignment)
        <div class="border-l-4 p-4 rounded bg-gray-50 border-gray-400 flex items-start gap-3">
            <x-icons.lucide.alert-circle class="w-5 h-5 flex-shrink-0 text-gray-500 mt-0.5" />
            <p class="text-sm text-gray-700">
                {{ $noActivePlanMessage ?? 'No tenés un plan activo. Contactá a tu entrenador.' }}
            </p>
        </div>
    @endif



    {{-- ACCESOS RÁPIDOS (solo mensajes y pagos para evitar duplicar progreso) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-6">
        <a href="{{ route('tenant.student.messages') }}" class="student-card">
            <x-icons.lucide.message-circle class="w-7 h-7 mb-2" style="color: var(--ftt-color-base)" />
            <h3>Mensajes</h3>
            <p>Contactar entrenador</p>
        </a>

        <a href="{{ route('tenant.student.payments') }}" class="student-card">
            <x-icons.lucide.credit-card class="w-7 h-7 mb-2" style="color: var(--ftt-color-base)" />
            <h3>Pagos</h3>
            <p>Historial y facturas</p>
        </a>
    </div>

    {{-- WORKOUT DE HOY + PROGRESO --}}
    @if ($assignment && $todayWorkout)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Columna izquierda: Workout de hoy --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl p-6" style="border: 1px solid #e5e7eb; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);">
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
                                <span class="text-green-600 font-medium flex items-center gap-1">
                                    <x-icons.lucide.check-circle class="w-4 h-4" />
                                    Completado
                                </span>
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
                <div class="bg-white rounded-xl p-6" style="border: 1px solid #e5e7eb; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);">
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
                                <div class="grid grid-cols-2 gap-3 mb-4">
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

                                {{-- Info de rutina sutil --}}
                                <div class="space-y-2 pt-3 border-t border-gray-100">
                                    <p class="text-xs text-gray-500">Tu rutina</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $assignment->name }}</p>
                                    <div class="flex gap-2 text-xs text-gray-600">
                                        <span>{{ $assignment->exercises_by_day->count() }} días</span>
                                        <span class="text-gray-400">·</span>
                                        <span>{{ $assignment->exercises_by_day->flatten(1)->count() }} ejercicios</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 pt-2">
                                        <a href="{{ route('tenant.student.download-plan', $assignment->uuid) }}"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium text-white transition hover:opacity-90"
                                            style="background-color: var(--ftt-color-base);">
                                            <x-icons.lucide.file-down class="w-3 h-3" />
                                            Descargar
                                        </a>
                                        <a href="{{ route('tenant.student.plan-detail', $assignment->uuid) }}"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium border border-gray-300 bg-white text-gray-700 transition hover:bg-gray-50">
                                            <x-icons.lucide.eye class="w-3 h-3" />
                                            Ver
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-4">Sin datos de progreso</p>
                    @endif
                </div>

            </div>
        </div>
    @endif

    {{-- PROGRESO MENSUAL (Fallback si no hay workout) --}}
    @if ($assignment && !$todayWorkout)
        <div class="bg-white rounded-xl p-6 flex flex-col md:flex-row justify-between items-center gap-4" style="border: 1px solid #e5e7eb; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);">
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

{{-- HISTORIAL DE PLANES (al final de la página) --}}
@if ($assignment && count($planHistory) > 0)
    <div class="mt-6 bg-white rounded-xl overflow-hidden" x-data="{ expanded: true }" style="border: 1px solid #e5e7eb; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);">
        <button type="button" @click="expanded = !expanded"
            class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-3">
                <x-icons.lucide.calendar class="w-5 h-5" style="color: var(--ftt-color-base);" />
                <div class="text-left">
                    <h3 class="text-base font-semibold text-gray-900">Historial de planes</h3>
                    <p class="text-xs text-gray-500">{{ count($planHistory) }} {{ count($planHistory) === 1 ? 'plan' : 'planes' }} anteriores</p>
                </div>
            </div>
            <x-icons.lucide.chevron-down class="w-5 h-5 text-gray-400 transition-transform"
                x-bind:class="expanded ? 'rotate-180' : ''" />
        </button>

        <div x-show="expanded" class="border-t border-gray-200">
            <div class="px-6 py-4 space-y-2 max-h-80 overflow-y-auto">
                @foreach ($planHistory as $plan)
                    @if (!$plan['is_current'])
                        <div class="p-3 rounded-lg border border-gray-100">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <p class="font-medium text-sm text-gray-900">{{ $plan['plan_name'] }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $plan['starts_at']?->format('d/m/Y') ?? '—' }} → {{ $plan['ends_at']?->format('d/m/Y') ?? '—' }}
                                    </p>
                                    <div class="flex gap-1.5 items-center text-xs text-gray-600 mt-2">
                                        <span>{{ $plan['days_count'] }} días</span>
                                        <span class="text-gray-400">·</span>
                                        <span>{{ $plan['exercises_count'] }} ejercicios</span>
                                    </div>
                                </div>
                                <div class="flex items-start gap-1.5">
                                    @if ($plan['status'] === 'completed')
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-700 whitespace-nowrap">
                                            Completado
                                        </span>
                                    @elseif ($plan['status'] === 'active')
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 whitespace-nowrap">
                                            Activo
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-700 whitespace-nowrap">
                                            {{ ucfirst($plan['status']) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endif

</div>
