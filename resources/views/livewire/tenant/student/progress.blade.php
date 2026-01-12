<div class="space-y-6 md:space-y-8">

    {{-- ENCABEZADO --}}
    <x-student-header
        title="Progreso"
        subtitle="Resumen de tus entrenamientos y métricas personales"
        icon="trending-up"
        :student="$student" />

    {{-- Bloque de resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-5">
            <div class="flex items-center justify-between mb-2">
                <x-icons.lucide.dumbbell class="w-8 h-8 text-green-600" />
                <h2 class="text-3xl font-bold text-green-600">{{ $sessionsThisMonth }}</h2>
            </div>
            <p class="text-sm text-gray-500">Entrenamientos este mes</p>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <div class="flex items-center justify-between mb-2">
                <x-icons.lucide.check-circle class="w-8 h-8 text-blue-600" />
                <h2 class="text-3xl font-bold text-blue-600">{{ $totalSessions }}</h2>
            </div>
            <p class="text-sm text-gray-500">Total completados</p>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <div class="flex items-center justify-between mb-2">
                <x-icons.lucide.target class="w-8 h-8 text-orange-500" />
                <h2 class="text-3xl font-bold text-orange-500">{{ $adherence }}%</h2>
            </div>
            <p class="text-sm text-gray-500">Adherencia promedio</p>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <div class="flex items-center justify-between mb-2">
                <x-icons.lucide.weight class="w-8 h-8 text-purple-600" />
                <h2 class="text-3xl font-bold text-purple-600">
                    @if($lastWeight)
                        {{ number_format($lastWeight, 1) }} kg
                    @else
                        —
                    @endif
                </h2>
            </div>
            <p class="text-sm text-gray-500">Peso actual</p>
            @if($weightChange !== null)
                <p class="text-xs mt-1 font-semibold {{ $weightChange < 0 ? 'text-green-600' : ($weightChange > 0 ? 'text-red-500' : 'text-gray-500') }}">
                    {{ $weightChange > 0 ? '+' : '' }}{{ number_format($weightChange, 1) }} kg
                </p>
            @endif
        </div>
    </div>

    {{-- Métricas corporales --}}
    @if($initialWeight || $heightCm)
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center gap-2 mb-4">
                <x-icons.lucide.activity class="w-5 h-5 text-gray-700" />
                <h3 class="text-lg font-semibold text-gray-700">Métricas corporales</h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @if($initialWeight)
                    <div>
                        <p class="text-xs text-gray-500">Peso inicial</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($initialWeight, 1) }} kg</p>
                    </div>
                @endif
                @if($heightCm)
                    <div>
                        <p class="text-xs text-gray-500">Altura</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($heightCm, 0) }} cm</p>
                    </div>
                @endif
                {{--
                @if($age)
                    <div>
                        <p class="text-xs text-gray-500">Edad</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $age }} años</p>
                    </div>
                @endif
                --}}
                @if($initialBMI)
                    <div>
                        <p class="text-xs text-gray-500">IMC inicial</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($initialBMI, 1) }}</p>
                    </div>
                @endif
                @if($currentBMI)
                    <div>
                        <p class="text-xs text-gray-500">IMC actual</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($currentBMI, 1) }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($currentBMI < 18.5) Bajo peso
                            @elseif($currentBMI < 25) Normal
                            @elseif($currentBMI < 30) Sobrepeso
                            @else Obesidad
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Evolución de peso --}}
    @if(count($weightHistory) > 0)
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center gap-2 mb-6">
                <x-icons.lucide.trending-down class="w-5 h-5 text-gray-700" />
                <h3 class="text-lg font-semibold text-gray-700">Evolución de peso</h3>
            </div>

            <div class="space-y-4">
                {{-- Gráfico de línea con SVG --}}
                @php
                    $weights = array_column($weightHistory, 'weight');
                    $minWeight = min($weights);
                    $maxWeight = max($weights);
                    $range = $maxWeight - $minWeight;
                    $range = $range > 0 ? $range : 1;

                    // Dimensiones del gráfico
                    $chartWidth = 600;
                    $chartHeight = 300;
                    $padding = 50;
                    $innerWidth = $chartWidth - ($padding * 2);
                    $innerHeight = $chartHeight - ($padding * 2);

                    // Calcular puntos
                    $points = [];
                    $dataPoints = [];
                    $numPoints = count($weightHistory);

                    foreach ($weightHistory as $index => $entry) {
                        if ($numPoints > 1) {
                            $x = $padding + ($innerWidth / ($numPoints - 1)) * $index;
                        } else {
                            $x = $padding + ($innerWidth / 2);
                        }
                        $y = $padding + $innerHeight - (($entry['weight'] - $minWeight) / $range) * $innerHeight;
                        $points[] = "$x,$y";
                        $dataPoints[] = [
                            'x' => $x,
                            'y' => $y,
                            'weight' => $entry['weight'],
                            'label' => $entry['label'],
                            'isInitial' => $entry['isInitial'] ?? false,
                        ];
                    }
                    $pathD = 'M ' . implode(' L ', $points);

                    // Calcular puntos del área (bottom)
                    $bottomRight = end($points);
                    $bottomLeft = reset($points);
                    list($bottomRightX, $bottomRightY) = explode(',', $bottomRight);
                    list($bottomLeftX, $bottomLeftY) = explode(',', $bottomLeft);
                    $bottomRightY = $chartHeight - $padding;
                    $bottomLeftY = $chartHeight - $padding;
                    $areaPath = 'M ' . implode(' L ', $points) . " L $bottomRightX,$bottomRightY L $bottomLeftX,$bottomLeftY Z";
                @endphp

                <div class="overflow-x-auto -mx-6 px-6">
                    <svg width="{{ $chartWidth }}" height="{{ $chartHeight }}" class="w-full" style="min-width: 600px; background: white;">
                        <defs>
                            <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#9333ea;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#7c3aed;stop-opacity:1" />
                            </linearGradient>
                            <linearGradient id="areaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" style="stop-color:#9333ea;stop-opacity:0.2" />
                                <stop offset="100%" style="stop-color:#9333ea;stop-opacity:0" />
                            </linearGradient>
                        </defs>

                        {{-- Área bajo la curva (relleno suave) --}}
                        <path d="{{ $areaPath }}" fill="url(#areaGradient)" />

                        {{-- Línea de la curva --}}
                        <path d="{{ $pathD }}" fill="none" stroke="url(#lineGradient)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>

                        {{-- Puntos interactivos --}}
                        @foreach($dataPoints as $point)
                            <g class="group cursor-pointer">
                                {{-- Área de interacción invisible (más grande) --}}
                                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="8"
                                        fill="transparent" class="hover:fill-purple-200 transition-all" />

                                {{-- Punto visible --}}
                                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5"
                                        fill="{{ $point['isInitial'] ? '#3b82f6' : '#9333ea' }}"
                                        style="stroke: white; stroke-width: 2.5; cursor: pointer;"
                                        class="transition-all hover:r-6" />

                                {{-- Tooltip --}}
                                <text x="{{ $point['x'] }}" y="{{ $point['y'] - 15 }}"
                                      text-anchor="middle"
                                      style="font-size: 12px; font-weight: 600; fill: #374151; pointer-events: none; opacity: 0; transition: opacity 0.2s;"
                                      class="tooltip">
                                    {{ $point['weight'] }} kg
                                </text>
                            </g>
                        @endforeach

                        {{-- Etiquetas X (fechas) --}}
                        @foreach($dataPoints as $point)
                            <text x="{{ $point['x'] }}" y="{{ $chartHeight - 5 }}"
                                  text-anchor="middle" style="font-size: 11px; fill: #9ca3af;">
                                {{ $point['label'] }}
                            </text>
                        @endforeach

                        {{-- Línea del eje Y --}}
                        <line x1="{{ $padding - 5 }}" y1="{{ $padding }}" x2="{{ $padding - 5 }}" y2="{{ $chartHeight - $padding }}" stroke="#e5e7eb" stroke-width="1"/>

                        {{-- Etiquetas Y (pesos) --}}
                        <text x="{{ $padding - 15 }}" y="{{ $padding + 5 }}" text-anchor="end" style="font-size: 11px; fill: #9ca3af;">
                            {{ number_format($maxWeight, 0) }} kg
                        </text>
                        <text x="{{ $padding - 15 }}" y="{{ $chartHeight - $padding + 5 }}" text-anchor="end" style="font-size: 11px; fill: #9ca3af;">
                            {{ number_format($minWeight, 0) }} kg
                        </text>
                    </svg>
                </div>

                <style>
                    svg g:hover .tooltip {
                        opacity: 1 !important;
                    }
                </style>

                {{-- Leyenda --}}
                <div class="flex justify-between items-center text-sm text-gray-600 pt-4 border-t">
                    <div>
                        <span class="text-xs text-gray-500">Mínimo:</span>
                        <span class="font-semibold">{{ number_format($minWeight, 1) }} kg</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500">Máximo:</span>
                        <span class="font-semibold">{{ number_format($maxWeight, 1) }} kg</span>
                    </div>
                    @if($weightChange !== null)
                        <div>
                            <span class="text-xs text-gray-500">Cambio total:</span>
                            <span class="font-semibold {{ $weightChange < 0 ? 'text-green-600' : ($weightChange > 0 ? 'text-red-500' : 'text-gray-600') }}">
                                {{ $weightChange > 0 ? '+' : '' }}{{ number_format($weightChange, 1) }} kg
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Estadísticas adicionales --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center gap-2 mb-4">
                <x-icons.lucide.bar-chart-3 class="w-5 h-5 text-gray-700" />
                <h3 class="text-lg font-semibold text-gray-700">Promedios</h3>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Duración promedio</span>
                    <span class="font-semibold text-gray-900">{{ $avgDuration > 0 ? round($avgDuration) . ' min' : '—' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Evaluación promedio</span>
                    <span class="font-semibold text-gray-900">
                        @if($avgRating)
                            {{ number_format($avgRating, 1) }}/5
                        @else
                            —
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center gap-2 mb-4">
                <x-icons.lucide.bar-chart-2 class="w-5 h-5 text-gray-700" />
                <h3 class="text-lg font-semibold text-gray-700">Últimos 6 meses</h3>
            </div>

            {{-- Comparativa mensual --}}
            <div class="grid grid-cols-3 gap-3 mb-6 pb-6 border-b">
                <div class="text-center">
                    <p class="text-xs text-gray-500 mb-1">Mes anterior</p>
                    <p class="text-2xl font-semibold text-gray-700">{{ $sessionsLastMonth }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 mb-1">Mes actual</p>
                    <p class="text-2xl font-semibold text-green-600">{{ $sessionsThisMonth }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 mb-1">Cambio</p>
                    <p class="text-2xl font-semibold">
                        @php
                            $diff = $sessionsThisMonth - $sessionsLastMonth;
                        @endphp
                        <span class="{{ $diff >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $diff >= 0 ? '+' : '' }}{{ $diff }}
                        </span>
                    </p>
                </div>
            </div>

            @if(count($monthlyStats) > 0)
                <div class="space-y-2">
                    @foreach($monthlyStats as $stat)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">{{ $stat['month_name'] }}</span>
                            <div class="flex items-center gap-3">
                                <span class="font-semibold text-gray-900">{{ $stat['count'] }} entrenamientos</span>
                                <span class="text-gray-500">{{ round($stat['avg_duration']) }}min</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No hay datos suficientes</p>
            @endif
        </div>
    </div>

    {{-- Entrenamientos recientes --}}
    @if(count($recentWorkouts) > 0)
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center gap-2 mb-4">
                <x-icons.lucide.history class="w-5 h-5 text-gray-700" />
                <h3 class="text-lg font-semibold text-gray-700">Entrenamientos recientes</h3>
            </div>
            <div class="space-y-3">
                @foreach($recentWorkouts as $workout)
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-block px-2 py-1 rounded text-xs font-semibold text-white" style="background-color: var(--ftt-color-base);">
                                        Día {{ $workout['plan_day'] }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($workout['completed_at'])->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-gray-600">
                                    <span class="flex items-center gap-1">
                                        <x-icons.lucide.clock class="w-4 h-4" />
                                        {{ $workout['duration_minutes'] }} min
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <x-icons.lucide.list-checks class="w-4 h-4" />
                                        {{ $workout['exercises_completed'] }}/{{ $workout['total_exercises'] }} ejercicios
                                    </span>
                                    @if($workout['rating'])
                                        <span class="flex items-center gap-1">
                                            <x-icons.lucide.star class="w-4 h-4" />
                                            {{ $workout['rating'] }}/5
                                        </span>
                                    @endif
                                </div>
                                @if($workout['notes'])
                                    <p class="text-sm text-gray-500 mt-2 italic">"{{ Str::limit($workout['notes'], 100) }}"</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Mensaje de sin datos --}}
    @if($totalSessions === 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
            <p class="text-sm text-yellow-800">
                Aún no registrás entrenamientos completados. ¡Empezá tu primera rutina hoy!
            </p>
        </div>
    @endif
</div>
