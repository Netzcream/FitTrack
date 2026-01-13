{{-- Evolución de Peso --}}
@if($student && $student->exists && count($this->weightChartData) > 0)
    <flux:separator variant="subtle" class="mt-8" />
    <div class="flex items-center justify-between mb-4">
        <flux:heading size="lg">Evolución de Peso</flux:heading>
        <flux:modal.trigger name="add-weight-drawer">
            <flux:button size="sm" variant="outline" icon="plus">
                Registrar peso
            </flux:button>
        </flux:modal.trigger>
    </div>

    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
        @php
            $weightChartData = $this->weightChartData;
            $weights = array_column($weightChartData, 'weight');
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
            $numPoints = count($weightChartData);

            foreach ($weightChartData as $index => $entry) {
                if ($numPoints > 1) {
                    $x = $padding + ($innerWidth / ($numPoints - 1)) * $index;
                } else {
                    $x = $padding + ($innerWidth / 2);
                }
                $y = $padding + $innerHeight - (($entry['weight'] - $minWeight) / $range) * $innerHeight;
                $points[] = ['x' => $x, 'y' => $y];
                $dataPoints[] = [
                    'x' => $x,
                    'y' => $y,
                    'weight' => $entry['weight'],
                    'label' => $entry['label'],
                    'isInitial' => $entry['isInitial'] ?? false,
                ];
            }

            // Generar curva suave usando Catmull-Rom spline
            $pathD = '';
            if (count($points) > 0) {
                $pathD = 'M ' . $points[0]['x'] . ',' . $points[0]['y'];

                if (count($points) > 2) {
                    // Usar curvas suaves para múltiples puntos
                    for ($i = 0; $i < count($points) - 1; $i++) {
                        $p0 = $points[max(0, $i - 1)];
                        $p1 = $points[$i];
                        $p2 = $points[$i + 1];
                        $p3 = $points[min(count($points) - 1, $i + 2)];

                        // Calcular puntos de control para curva Catmull-Rom
                        $tension = 0.5; // Tensión de la curva (0.5 = curva suave)
                        $cp1x = $p1['x'] + ($p2['x'] - $p0['x']) / 6 * $tension;
                        $cp1y = $p1['y'] + ($p2['y'] - $p0['y']) / 6 * $tension;
                        $cp2x = $p2['x'] - ($p3['x'] - $p1['x']) / 6 * $tension;
                        $cp2y = $p2['y'] - ($p3['y'] - $p1['y']) / 6 * $tension;

                        $pathD .= ' C ' . $cp1x . ',' . $cp1y . ' ' . $cp2x . ',' . $cp2y . ' ' . $p2['x'] . ',' . $p2['y'];
                    }
                } elseif (count($points) == 2) {
                    // Línea recta para 2 puntos
                    $pathD .= ' L ' . $points[1]['x'] . ',' . $points[1]['y'];
                }
            }

            // Calcular área para relleno
            $areaPath = $pathD;
            if (count($points) > 0) {
                $lastPoint = end($points);
                $firstPoint = reset($points);
                $areaPath .= ' L ' . $lastPoint['x'] . ',' . ($chartHeight - $padding);
                $areaPath .= ' L ' . $firstPoint['x'] . ',' . ($chartHeight - $padding);
                $areaPath .= ' Z';
            }

            // Calcular cambio de peso
            $initialWeight = $weights[0];
            $currentWeight = end($weights);
            $weightChange = $currentWeight - $initialWeight;
        @endphp

        <div class="overflow-x-auto -mx-6 px-6">
            <svg width="{{ $chartWidth }}" height="{{ $chartHeight }}" class="w-full" style="min-width: 600px;">
                <defs>
                    <linearGradient id="lineGradientWeight" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" style="stop-color: {{ tenant_config('color_base', '#263d83') }}; stop-opacity:1" />
                        <stop offset="100%" style="stop-color: {{ tenant_config('color_dark', '#263d83') }}; stop-opacity:1" />
                    </linearGradient>
                    <linearGradient id="areaGradientWeight" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color: {{ tenant_config('color_base', '#263d83') }}; stop-opacity:0.2" />
                        <stop offset="100%" style="stop-color: {{ tenant_config('color_base', '#263d83') }}; stop-opacity:0" />
                    </linearGradient>
                </defs>

                {{-- Área bajo la curva (relleno suave) --}}
                <path d="{{ $areaPath }}" fill="url(#areaGradientWeight)" />

                {{-- Línea de la curva suave --}}
                <path d="{{ $pathD }}" fill="none" stroke="url(#lineGradientWeight)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>

                {{-- Puntos interactivos --}}
                @foreach($dataPoints as $point)
                    <g class="group cursor-pointer">
                        {{-- Área de interacción invisible (más grande) --}}
                        <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="10"
                                fill="transparent" style="transition: all 0.2s;" />

                        {{-- Punto visible --}}
                        <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5"
                                fill="{{ tenant_config('color_base', '#263d83') }}"
                                style="stroke: white; stroke-width: 2.5; cursor: pointer; transition: all 0.2s;" />

                        {{-- Tooltip --}}
                        <text x="{{ $point['x'] }}" y="{{ $point['y'] - 15 }}"
                              text-anchor="middle"
                              class="text-xs font-semibold fill-gray-700 dark:fill-gray-200 pointer-events-none opacity-0 transition-opacity group-hover:opacity-100 tooltip-weight">
                            {{ $point['weight'] }} kg
                        </text>
                    </g>
                @endforeach

                {{-- Etiquetas X (fechas) --}}
                @foreach($dataPoints as $point)
                    <text x="{{ $point['x'] }}" y="{{ $chartHeight - 5 }}"
                          text-anchor="middle" class="text-[11px] fill-gray-400 dark:fill-gray-500">
                        {{ $point['label'] }}
                    </text>
                @endforeach

                {{-- Línea del eje Y --}}
                <line x1="{{ $padding - 5 }}" y1="{{ $padding }}" x2="{{ $padding - 5 }}" y2="{{ $chartHeight - $padding }}" class="stroke-gray-300 dark:stroke-gray-600" stroke-width="1"/>

                {{-- Etiquetas Y (pesos) --}}
                <text x="{{ $padding - 15 }}" y="{{ $padding + 5 }}" text-anchor="end" class="text-[11px] fill-gray-400 dark:fill-gray-500">
                    {{ number_format($maxWeight, 0) }} kg
                </text>
                <text x="{{ $padding - 15 }}" y="{{ $chartHeight - $padding + 5 }}" text-anchor="end" class="text-[11px] fill-gray-400 dark:fill-gray-500">
                    {{ number_format($minWeight, 0) }} kg
                </text>
            </svg>
        </div>

        {{-- Leyenda --}}
        <div class="flex justify-between items-center text-sm text-gray-600 dark:text-gray-400 pt-4 border-t border-gray-200 dark:border-neutral-700">
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Mínimo:</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($minWeight, 1) }} kg</span>
            </div>
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Máximo:</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($maxWeight, 1) }} kg</span>
            </div>
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Cambio total:</span>
                <span class="font-semibold {{ $weightChange < 0 ? 'text-green-600 dark:text-green-400' : ($weightChange > 0 ? 'text-red-500 dark:text-red-400' : 'text-gray-600 dark:text-gray-400') }}">
                    {{ $weightChange > 0 ? '+' : '' }}{{ number_format($weightChange, 1) }} kg
                </span>
            </div>
        </div>
    </div>
@endif
