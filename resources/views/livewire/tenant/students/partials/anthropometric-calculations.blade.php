{{-- Cálculos Antropométricos --}}
@if($editMode && $student)
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Cálculos antropométricos</p>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    {{ $genderLabel ?? '—' }}
                    @if ($age !== null)
                        | {{ $age }} años
                    @endif
                    @if ($height !== null)
                        | {{ number_format($height / 100, 2) }} m
                    @endif
                    @if ($weight !== null)
                        | {{ $weight }} kg
                    @endif
                </p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-neutral-500 dark:text-neutral-400">Peso Ideal (PI)</span>
                <span class="font-medium text-neutral-800 dark:text-neutral-100">{{ $pi !== null ? number_format($pi, 1, ',', '.') . ' kg' : '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-neutral-500 dark:text-neutral-400">% Peso Ideal (PPI)</span>
                <span class="font-medium text-neutral-800 dark:text-neutral-100">{{ $ppi !== null ? number_format($ppi, 1, ',', '.') . ' %' : '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-neutral-500 dark:text-neutral-400">Índice Masa Corporal (IMC)</span>
                <span class="font-medium text-neutral-800 dark:text-neutral-100">{{ $bmi !== null ? number_format($bmi, 1, ',', '.') . ' kg/m²' : '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-neutral-500 dark:text-neutral-400">Área Sup. Corporal (ASC)</span>
                <span class="font-medium text-neutral-800 dark:text-neutral-100">{{ $asc !== null ? number_format($asc, 2, ',', '.') . ' m²' : '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-neutral-500 dark:text-neutral-400">Masa Corporal Magra (MCM)</span>
                <span class="font-medium text-neutral-800 dark:text-neutral-100">{{ $mcm !== null ? number_format($mcm, 1, ',', '.') . ' kg' : '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-neutral-500 dark:text-neutral-400">Agua Corporal Total (ACT)</span>
                <span class="font-medium text-neutral-800 dark:text-neutral-100">{{ $act !== null ? number_format($act, 1, ',', '.') . ' L' : '—' }}</span>
            </div>
        </div>
        <div class="border-t border-neutral-200 dark:border-neutral-800 pt-3 space-y-2 text-sm">
            <p class="text-neutral-700 dark:text-neutral-200 font-medium">Peso ideal (rango IMC 20-25)</p>
            <div class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400">
                <span>IMC 20 kg/m²</span>
                <span class="font-medium text-neutral-800 dark:text-neutral-100">{{ $bmi20 !== null ? number_format($bmi20, 1, ',', '.') . ' kg' : '—' }}</span>
            </div>
            <div class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400">
                <span>IMC 25 kg/m²</span>
                <span class="font-medium text-neutral-800 dark:text-neutral-100">{{ $bmi25 !== null ? number_format($bmi25, 1, ',', '.') . ' kg' : '—' }}</span>
            </div>
        </div>
        @if ($obesityType)
            <div class="border-t border-neutral-200 dark:border-neutral-800 pt-3">
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">Clasificación</p>
                <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
                    {{ $obesityType }}
                </div>
            </div>
        @endif
    </div>
@endif
