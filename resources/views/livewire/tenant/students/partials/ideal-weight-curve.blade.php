{{-- Curva Peso Ideal --}}
@if($editMode && $student)
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Curva peso ideal</p>
                @if ($idealRange)
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Centro ideal: {{ $idealCenter }} kg</p>
                @else
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Se necesita altura para estimar rango</p>
                @endif
            </div>
            <div class="flex items-center gap-3 text-xs text-neutral-500 dark:text-neutral-400">
                <span class="inline-flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: var(--ftt-color-base);"></span>
                    Ideal
                </span>
                <span class="inline-flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-neutral-400 dark:bg-neutral-500"></span>
                    Actual
                </span>
            </div>
        </div>
        @if ($idealRange && count($chartIdeal))
            <div class="space-y-3" wire:key="chart-wrapper-{{ $chartKey }}">
                <div id="weight-ideal-chart-{{ $chartKey }}" data-apex-placeholder
                    x-data x-init="$nextTick(() => { if (typeof window.initApexPlaceholders === 'function') window.initApexPlaceholders(); })"
                    data-apex-force="true"
                    data-chart-type="area"
                    data-chart-height="170"
                    data-chart-stroke="1.6"
                    data-chart-marker-size="0"
                    data-chart-fill-opacity="0.50"
                    data-chart-ymin="0"
                    data-chart-ymax="5"
                    data-chart-xaxis-type="numeric"
                    data-chart-xmin="{{ $scaleMin }}"
                    data-chart-xmax="{{ $scaleMax }}"
                    data-chart-sparkline="true"
                    data-chart-xlabels="false"
                    data-chart-ylabels="false"
                    data-chart-grid="false"
                    data-chart-colors='@json([tenant_config("color_base", "#263d83")])'
                    data-chart-x-annotations='@json($chartAnnotations)'
                    data-series='@json($chartSeries)'
                    class="h-[170px]"></div>
                <div class="grid grid-cols-4 text-[11px] text-neutral-500 dark:text-neutral-400">
                    <span class="text-left">{{ $scaleMin }} kg</span>
                    <span class="text-center">{{ $idealCenter }} kg</span>
                    <span class="text-center">{{ $weight !== null ? $weight : '-' }} kg</span>
                    <span class="text-right">{{ $scaleMax }} kg</span>
                </div>
                @if ($weight === null)
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Anade peso para ubicar el marcador.</p>
                @endif
            </div>
        @else
            <p class="text-xs text-neutral-500 dark:text-neutral-400">Anade altura para mostrar la curva y el rango ideal.</p>
        @endif
    </div>
@endif
