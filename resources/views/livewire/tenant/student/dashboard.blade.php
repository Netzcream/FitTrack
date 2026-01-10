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
                No tenés un plan activo. Contactá a tu entrenador.
            </p>
        </div>
    @endif

    {{-- PROGRESO MENSUAL --}}
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
            @if ($todaySession)
                <x-icons.lucide.rotate-cw class="w-4 h-4" />
                Continuar entrenamiento
            @else
                <x-icons.lucide.zap class="w-4 h-4" />
                Comenzar entrenamiento
            @endif
        </button>
    </div>

    {{-- RUTINA Y PLANES --}}
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

        {{-- Lista de días eliminada del dashboard; ver detalle en pantalla dedicada --}}
    @endif

    {{-- PLANES ANTERIORES --}}
    @if (!empty($assignmentsHistory) && $assignmentsHistory->count() > 1)
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200 space-y-4">
            <div class="flex items-center gap-2 text-gray-700 font-semibold">
                <x-icons.lucide.history class="w-5 h-5" />
                <span>Planes anteriores</span>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($assignmentsHistory->skip(1) as $past)
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <p class="font-medium text-gray-900">{{ $past->name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $past->starts_at?->format('d/m/Y') ?? '—' }}
                                @if ($past->ends_at)
                                    – {{ $past->ends_at->format('d/m/Y') }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('tenant.student.download-plan', $past->uuid) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-semibold text-white shadow-sm transition"
                                style="background-color: var(--ftt-color-base);">
                                <x-icons.lucide.file-down class="w-4 h-4" />
                                Descargar
                            </a>
                        </div>
                    </div>
                @endforeach
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
