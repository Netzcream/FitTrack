<div class="space-y-8">
    {{-- Encabezado --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">📈 Progreso</h1>
        <p class="text-gray-500">Resumen de tus entrenamientos y métricas personales</p>
    </div>

    {{-- Bloque de resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-5 text-center">
            <h2 class="text-3xl font-bold text-green-600">{{ $sessionsThisMonth }}</h2>
            <p class="text-sm text-gray-500">Entrenamientos este mes</p>
        </div>

        <div class="bg-white rounded-xl shadow p-5 text-center">
            <h2 class="text-3xl font-bold text-blue-600">{{ $totalSessions }}</h2>
            <p class="text-sm text-gray-500">Total completados</p>
        </div>

        <div class="bg-white rounded-xl shadow p-5 text-center">
            <h2 class="text-3xl font-bold text-orange-500">{{ $adherence }}%</h2>
            <p class="text-sm text-gray-500">Adherencia promedio</p>
        </div>

        <div class="bg-white rounded-xl shadow p-5 text-center">
            <h2 class="text-3xl font-bold text-purple-600">
                @if($lastWeight)
                    {{ number_format($lastWeight, 1) }} kg
                @else
                    —
                @endif
            </h2>
            <p class="text-sm text-gray-500">Último peso registrado</p>
        </div>
    </div>

    {{-- Comparativa mensual --}}
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Comparativa mensual</h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="p-3 border rounded-lg">
                <p class="text-sm text-gray-500">Mes anterior</p>
                <p class="text-2xl font-semibold text-gray-700">{{ $sessionsLastMonth }}</p>
            </div>

            <div class="p-3 border rounded-lg">
                <p class="text-sm text-gray-500">Mes actual</p>
                <p class="text-2xl font-semibold text-green-600">{{ $sessionsThisMonth }}</p>
            </div>

            <div class="p-3 border rounded-lg">
                <p class="text-sm text-gray-500">Cambio</p>
                <p class="text-2xl font-semibold">
                    @php
                        $diff = $sessionsThisMonth - $sessionsLastMonth;
                    @endphp
                    <span class="{{ $diff >= 0 ? 'text-green-600' : 'text-red-500' }}">
                        {{ $diff >= 0 ? '+' : '' }}{{ $diff }}
                    </span>
                </p>
            </div>

            <div class="p-3 border rounded-lg">
                <p class="text-sm text-gray-500">Grasa corporal</p>
                <p class="text-2xl font-semibold text-purple-600">
                    {{ $lastBodyFat ? number_format($lastBodyFat, 1) . '%' : '—' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Mensaje de sin datos --}}
    @if($totalSessions === 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
            <p class="text-sm text-yellow-800">
                ⚠️ Aún no registrás entrenamientos completados. ¡Empezá tu primera rutina hoy!
            </p>
        </div>
    @endif
</div>
