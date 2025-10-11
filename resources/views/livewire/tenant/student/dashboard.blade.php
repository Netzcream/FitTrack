<div class="space-y-6">
    {{-- Bloque principal --}}
    <div class="bg-white rounded-2xl shadow p-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">
                {{ $trainingsThisMonth }} entrenamientos completados este mes
            </h2>
            <p class="text-sm text-gray-500">
                Tu meta: {{ $goalThisMonth }} entrenamientos mensuales.
            </p>
            <div class="w-56 bg-gray-200 h-2 rounded-full mt-2">
                <div class="bg-green-500 h-2 rounded-full transition-all duration-500"
                    style="width: {{ min(100, ($trainingsThisMonth / max(1,$goalThisMonth)) * 100) }}%">
                </div>
            </div>
        </div>

        <button wire:click="startOrContinueWorkout"
            class="bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-xl font-medium flex items-center gap-2 transition">
            @if($todaySession)
                🔁 Continuar entrenamiento de hoy
            @else
                💪 Comenzar nuevo entrenamiento
            @endif
        </button>
    </div>

    {{-- Estado del plan --}}
    <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">🏋️ Plan actual</h3>
        @if($assignment)
            <p class="text-gray-800 font-medium">{{ $currentRoutine }}</p>
            <p class="text-sm text-gray-500">
                Desde {{ $assignment->start_date->format('d/m/Y') }}
            </p>
        @else
            <p class="text-sm text-gray-500">
                No tenés un plan activo en este momento.
            </p>
        @endif
    </div>

    {{-- Accesos rápidos --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('tenant.student.workout-today') }}"
            class="bg-white hover:bg-green-50 border rounded-xl shadow-sm p-5 flex flex-col items-center text-center transition">
            <span class="text-3xl mb-2">🏋️‍♀️</span>
            <h3 class="font-semibold text-gray-700">Mi rutina</h3>
            <p class="text-xs text-gray-500">Ver y marcar ejercicios</p>
        </a>

        <a href="{{ route('tenant.student.progress') }}"
            class="bg-white hover:bg-blue-50 border rounded-xl shadow-sm p-5 flex flex-col items-center text-center transition">
            <span class="text-3xl mb-2">📈</span>
            <h3 class="font-semibold text-gray-700">Progreso</h3>
            <p class="text-xs text-gray-500">Estadísticas y evolución</p>
        </a>

        <a href="{{ route('tenant.student.messages') }}"
            class="bg-white hover:bg-purple-50 border rounded-xl shadow-sm p-5 flex flex-col items-center text-center transition">
            <span class="text-3xl mb-2">💬</span>
            <h3 class="font-semibold text-gray-700">Mensajes</h3>
            <p class="text-xs text-gray-500">Habla con tu entrenador</p>
        </a>
    </div>

    {{-- Alertas --}}
    <div class="space-y-3">
        @if($goalThisMonth && $trainingsThisMonth >= $goalThisMonth)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                <p class="text-sm font-medium text-yellow-700">
                    🎉 ¡Felicitaciones! Completaste tu objetivo mensual. Nueva meta: {{ $goalThisMonth + 3 }} entrenamientos.
                </p>
            </div>
        @endif

        @if($hasPendingPayment)
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                <p class="text-sm text-red-700">
                    ⚠️ Abono próximo a vencer. <a href="{{ route('tenant.student.payments') }}" class="underline font-medium">Pagar ahora</a>
                </p>
            </div>
        @endif

        @if(!$assignment)
            <div class="bg-gray-50 border-l-4 border-gray-400 p-4 rounded">
                <p class="text-sm text-gray-700">
                    ⚠️ No tenés un plan de entrenamiento activo. Contactá a tu entrenador para que te asigne uno.
                </p>
            </div>
        @endif
    </div>
</div>
