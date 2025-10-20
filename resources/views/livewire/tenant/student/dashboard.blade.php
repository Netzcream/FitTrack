<div class="space-y-6 md:space-y-8">

    {{-- ENCABEZADO --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                🏋️ Panel de entrenamiento
            </h1>
            <p class="text-gray-500">
                Resumen de tu actividad y tus próximos pasos
            </p>
        </div>


        @if ($student->hasMedia('avatar'))
            <img src="{{ $student->getFirstMediaUrl('avatar', 'thumb') }}" alt="{{ $student->full_name }}"
                class="w-12 h-12 rounded-full border border-gray-200 object-cover shadow-sm">
        @endif
    </div>

    {{-- ALERTAS (ahora arriba del contenido principal) --}}
    @if ($goalThisMonth && $trainingsThisMonth >= $goalThisMonth)
        <div class="rounded p-4 border-l-4"
            style="border-color: var(--ftt-color-base);
                    background-color: var(--ftt-color-base-transparent);">
            <p class="text-sm font-medium text-gray-800">
                🎉 ¡Excelente! Completaste tu meta mensual 🎯
            </p>
        </div>
    @endif

    @if ($hasPendingPayment)
        <div class="border-l-4 p-4 rounded bg-red-50 border-red-500">
            <p class="text-sm text-red-700">
                ⚠️ Tenés un pago pendiente.
                <a href="{{ route('tenant.student.payments') }}" class="underline">Ver pagos</a>
            </p>
        </div>
    @endif

    @if (!$assignment)
        <div class="border-l-4 p-4 rounded bg-gray-50 border-gray-400">
            <p class="text-sm text-gray-700">
                ⚠️ No tenés un plan activo. Contactá a tu entrenador.
            </p>
        </div>
    @endif

    {{-- PROGRESO MENSUAL --}}
    <div
        class="bg-white rounded-2xl shadow-md p-6 flex flex-col md:flex-row justify-between items-center gap-4 border border-gray-200">
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

        <button wire:click="startOrContinueWorkout" class="start-button">
            @if ($todaySession)
                🔁 Continuar entrenamiento
            @else
                💪 Comenzar nuevo entrenamiento
            @endif
        </button>
    </div>

    {{-- PLAN ACTUAL --}}
    @if ($assignment)
        <div
            class="bg-white rounded-2xl shadow-md p-6 border border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold mb-2 flex items-center gap-2" style="color: var(--ftt-color-base)">
                    🏋️ <span>Plan actual</span>
                </h3>
                <p class="font-medium text-gray-800">
                    {{ $assignment->name }}
                    <span class="text-sm text-gray-500 ml-1">
                        ({{ $assignment->version_label }})
                    </span>
                </p>
                <p class="text-sm text-gray-500">
                    Desde {{ $assignment->assigned_from?->format('d/m/Y') ?? '—' }}
                </p>
            </div>

            {{-- BOTÓN DESCARGAR --}}
            <a href="{{ route('tenant.student.download-plan', $assignment->uuid) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white shadow-sm transition"
                style="background-color: var(--ftt-color-base);">
                <x-icons.lucide.file-down class="w-4 h-4" />
                Descargar PDF
            </a>
        </div>
    @endif


    {{-- ACCESOS RÁPIDOS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
        <a href="{{ route('tenant.student.workout-today') }}" class="student-card">
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
