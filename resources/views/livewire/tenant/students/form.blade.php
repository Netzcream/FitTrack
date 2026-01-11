<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-6">

            {{-- Header sticky --}}
            <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
                <div class="flex items-center justify-between gap-4 max-w-3xl">
                    <div>
                        <flux:heading size="xl" level="1">
                            {{ $editMode ? __('students.edit_title') : __('students.new_title') }}
                        </flux:heading>
                        <flux:subheading size="lg" class="mb-6">
                            {{ $editMode ? __('students.edit_subheading') : __('students.new_subheading') }}
                        </flux:subheading>
                    </div>
                    <div class="flex items-center gap-3">
                        <flux:checkbox size="sm" label="{{ __('site.back_list') }}" wire:model.live="back" />
                        <flux:button as="a" variant="ghost" href="{{ route('tenant.dashboard.students.index') }}" size="sm">
                            {{ __('site.back') }}
                        </flux:button>
                        <flux:button type="submit" size="sm">
                            {{ $editMode ? __('students.update_button') : __('students.create_button') }}
                        </flux:button>
                    </div>
                </div>
                <flux:separator variant="subtle" class="mt-2" />
            </div>

            {{-- Contenido --}}
            <div class="max-w-3xl space-y-4">

                {{-- Resumen del plan actual --}}
                @if ($editMode && $this->currentPlan)
                    @php
                        $plan = $this->currentPlan;
                        $now = now();
                        $isExpired = $plan->ends_at && $plan->ends_at->isPast();
                        $daysRemaining = $plan->ends_at ? (int) $now->diffInDays($plan->ends_at, false) : null;
                    @endphp
                    <div class="rounded-lg p-4 border" style="background-color: var(--ftt-color-base-transparent); border-color: var(--ftt-color-base);">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-neutral-100">{{ $plan->name }}</h3>
                                    @if ($plan->is_active && !$isExpired)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Activo
                                        </span>
                                    @elseif ($isExpired)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            Vencido
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                            Inactivo
                                        </span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-700 dark:text-neutral-300">
                                    @if ($plan->starts_at)
                                        <span>Inicio: {{ $plan->starts_at->format('d/m/Y') }}</span>
                                    @endif
                                    @if ($plan->ends_at)
                                        <span>Vence: {{ $plan->ends_at->format('d/m/Y') }}</span>
                                        @if (!$isExpired && $daysRemaining !== null)
                                            <span class="font-medium">
                                                @if ($daysRemaining == 0)
                                                    (vence hoy)
                                                @elseif ($daysRemaining == 1)
                                                    (1 día restante)
                                                @elseif ($daysRemaining > 1)
                                                    ({{ $daysRemaining }} días restantes)
                                                @endif
                                            </span>
                                        @elseif ($isExpired && $daysRemaining !== null)
                                            <span class="font-medium text-red-600 dark:text-red-400">
                                                (hace {{ abs($daysRemaining) }} {{ abs($daysRemaining) == 1 ? 'día' : 'días' }})
                                            </span>
                                        @endif
                                    @else
                                        <span>Sin fecha de vencimiento</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Estado y acciones de plan --}}
                @php
                    $statusColors = [
                        'active' => 'bg-emerald-500',
                        'paused' => 'bg-amber-500',
                        'inactive' => 'bg-gray-400',
                        'prospect' => 'bg-sky-500',
                    ];
                @endphp
                <div class="flex flex-wrap items-end justify-between gap-3 pb-4 border-b border-gray-200 dark:border-neutral-700">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-neutral-300 flex items-center gap-2">
                            {{ __('students.status') }}
                            <span class="h-2.5 w-2.5 rounded-full flex-shrink-0 {{ $statusColors[$status] ?? 'bg-gray-300' }}"></span>
                        </label>
                        <flux:select wire:model.live="status" size="sm" class="w-auto">
                            <option value="active">{{ __('students.status.active') }}</option>
                            <option value="paused">{{ __('students.status.paused') }}</option>
                            <option value="inactive">{{ __('students.status.inactive') }}</option>
                            <option value="prospect">{{ __('students.status.prospect') }}</option>
                        </flux:select>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($editMode && $student)
                            <flux:modal.trigger name="assign-plan-drawer">
                                <flux:button size="sm" variant="outline" icon="plus">
                                    {{ __('students.assign_plan') }}
                                </flux:button>
                            </flux:modal.trigger>
                            <flux:button size="sm" variant="ghost" as="a" wire:navigate
                                href="{{ route('tenant.dashboard.students.plans-history', ['student' => $student->uuid, 'back' => 'form']) }}">
                                {{ __('students.view_plans') }}
                            </flux:button>
                        @else
                            <flux:button size="sm" variant="outline" disabled>
                                {{ __('students.assign_plan') }}
                            </flux:button>
                        @endif
                    </div>
                </div>

                {{-- Nombres --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:input wire:model.defer="first_name" :label="__('students.first_name')" required autocomplete="off" />
                    </div>
                    <div>
                        <flux:input wire:model.defer="last_name" :label="__('students.last_name')" required autocomplete="off" />
                    </div>
                </div>

                {{-- Avatar --}}
                <div>
                    <flux:label>{{ __('students.avatar') }}</flux:label>
                    <div class="flex items-center gap-4">
                        <div class="relative group cursor-pointer h-20 w-20 rounded-full overflow-hidden border border-gray-300 dark:border-neutral-700 bg-gray-100 dark:bg-neutral-800 flex items-center justify-center"
                            onclick="document.getElementById('avatarInput').click()">
                            @if ($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" class="h-full w-full object-cover" alt="avatar">
                            @elseif ($currentAvatarUrl)
                                <img src="{{ $currentAvatarUrl }}" class="h-full w-full object-cover" alt="avatar">
                            @else
                                <span class="text-sm font-medium text-gray-500 dark:text-neutral-400">
                                    {{ strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1)) }}
                                </span>
                            @endif

                            <div wire:loading.flex wire:target="avatar"
                                class="absolute inset-0 bg-black/60 text-white flex flex-col items-center justify-center text-xs font-medium z-10">
                                <x-icons.lucide.loader class="animate-spin h-5 w-5" />
                            </div>

                            <div
                                class="absolute inset-0 bg-black/40 text-white opacity-0 group-hover:opacity-100 transition flex flex-col items-center justify-center text-xs font-medium z-0">
                                <x-icons.lucide.upload class="h-5 w-5" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <input id="avatarInput" type="file" wire:model="avatar" accept="image/*" class="hidden" />

                            <flux:button size="sm" variant="outline" onclick="document.getElementById('avatarInput').click()">
                                {{ $avatar || $currentAvatarUrl ? __('students.change_avatar') : __('students.upload_avatar') }}
                            </flux:button>

                            @if ($avatar || $currentAvatarUrl)
                                <flux:button size="sm" variant="ghost" wire:click="deleteAvatar">
                                    {{ __('students.remove_avatar') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Contacto básico --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <flux:input wire:model.defer="email" :label="__('students.email')" type="email" required autocomplete="off" />
                    </div>
                    <div>
                        <flux:input wire:model.defer="phone" :label="__('students.phone')" autocomplete="off" />
                    </div>
                </div>

                {{-- Plan comercial y facturación --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <flux:select wire:model.defer="commercial_plan_id" :label="__('students.plan')">
                            <option value="">{{ __('common.none') }}</option>
                            @foreach ($plans as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:select wire:model.defer="billing_frequency" :label="__('students.billing_frequency')">
                            <option value="monthly">{{ __('students.monthly') }}</option>
                            <option value="quarterly">{{ __('students.quarterly') }}</option>
                            <option value="yearly">{{ __('students.yearly') }}</option>
                        </flux:select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:select wire:model.defer="account_status" :label="__('students.account_status')">
                            <option value="on_time">{{ __('students.account_status_on_time') }}</option>
                            <option value="due">{{ __('students.account_status_due') }}</option>
                            <option value="review">{{ __('students.account_status_review') }}</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:select wire:model.defer="is_user_enabled" :label="__('students.user_enabled')">
                            <option value="1">{{ __('common.yes') }}</option>
                            <option value="0">{{ __('common.no') }}</option>
                        </flux:select>
                    </div>
                </div>

                <flux:textarea wire:model.defer="goal" :label="__('students.goal')" rows="3"
                    placeholder="{{ __('students.goal_placeholder') }}" />

                {{-- Data unificada --}}
                <flux:separator variant="subtle" class="mt-8" />
                <flux:heading size="lg">{{ __('students.personal_data_section') }}</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:input type="date" wire:model.defer="data.birth_date" :label="__('students.birth_date')" />
                    </div>
                    <div>
                        <flux:select wire:model.defer="data.gender" :label="__('students.gender')">
                            <option value="">{{ __('common.select') }}</option>
                            <option value="male">{{ __('students.gender_male') }}</option>
                            <option value="female">{{ __('students.gender_female') }}</option>
                            <option value="other">{{ __('students.gender_other') }}</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:input type="number" step="1" wire:model.live.debounce.750ms="data.height_cm" :label="__('students.height_cm')" />
                    </div>
                    <div>
                        <flux:input type="number" step="0.1" wire:model.live.debounce.750ms="data.weight_kg" :label="__('students.weight_kg')" />
                    </div>
                    @php
                        $height = $data['height_cm'] ?? null;
                        $weight = $data['weight_kg'] ?? null;

                        // Convertir cadenas vacías en null y asegurar que sean numéricos
                        $height = ($height !== '' && $height !== null) ? (float) $height : null;
                        $weight = ($weight !== '' && $weight !== null) ? (float) $weight : null;

                        $bmi = null;

                        if ($height > 0 && $weight > 0) {
                            $heightM = $height / 100;
                            $bmi = round($weight / ($heightM ** 2), 1);
                        }

                        $idealRange = null;
                        $scaleMin = null;
                        $scaleMax = null;
                        $currentPosition = null;
                        $idealCenter = null;
                        $chartIdeal = [];
                        $chartSeries = [];
                        $chartAnnotations = [];
                        $pi = null;
                        $ppi = null;
                        $asc = null;
                        $mcm = null;
                        $act = null;
                        $bmi20 = null;
                        $bmi25 = null;
                        $obesityType = null;
                        $genderLabel = null;
                        $age = null;
                        // Generar un key único que cambie cuando cambien altura o peso
                        $chartKey = md5(json_encode(['h' => $height, 'w' => $weight]));

                        if ($height !== null && $height > 0) {
                            $heightM = $height / 100;
                            $idealMinKg = round(18.5 * ($heightM ** 2), 1);
                            $idealMaxKg = round(24.9 * ($heightM ** 2), 1);
                            $idealRange = [$idealMinKg, $idealMaxKg];
                            $idealCenter = round(($idealMinKg + $idealMaxKg) / 2, 1);

                            if (!empty($data['birth_date'])) {
                                $birthDate = \Carbon\Carbon::parse($data['birth_date']);
                                $age = (int) abs(now()->diffInYears($birthDate));
                            }

                            if (!empty($data['gender'])) {
                                $genderLabel = $data['gender'] === 'female' ? 'mujer' : ($data['gender'] === 'male' ? 'hombre' : 'otro');
                            }

                            if (!empty($data['gender']) && $height !== null && $height > 0) {
                                if ($data['gender'] === 'female') {
                                    $pi = 45.5 + (0.9 * ($height - 152.4));
                                } elseif ($data['gender'] === 'male') {
                                    $pi = 50 + (0.9 * ($height - 152.4));
                                } else {
                                    $pi = 47.75 + (0.9 * ($height - 152.4));
                                }
                                $pi = round($pi, 1);
                            }

                            if ($weight !== null && $weight > 0 && $pi) {
                                $ppi = round(($weight / $pi) * 100, 1);
                            }

                            if ($weight !== null && $weight > 0 && $height !== null && $height > 0) {
                                $asc = round(sqrt(($height * $weight) / 3600), 1);
                            }

                            if ($weight !== null && $weight > 0 && $height !== null && $height > 0) {
                                if (!empty($data['gender']) && $data['gender'] === 'female') {
                                    $mcm = (1.07 * $weight) - (148 * (($weight / $height) ** 2));
                                } elseif (!empty($data['gender']) && $data['gender'] === 'male') {
                                    $mcm = (1.10 * $weight) - (128 * (($weight / $height) ** 2));
                                } else {
                                    $mcm = (1.085 * $weight) - (138 * (($weight / $height) ** 2));
                                }
                                $mcm = round($mcm, 1);
                            }

                            if ($weight !== null && $weight > 0 && $height !== null && $height > 0) {
                                if (!empty($data['gender']) && $data['gender'] === 'female') {
                                    $act = -2.097 + (0.1069 * $height) + (0.2466 * $weight);
                                } elseif (!empty($data['gender']) && $data['gender'] === 'male' && $age !== null) {
                                    $act = 2.447 - (0.09156 * $age) + (0.1074 * $height) + (0.3362 * $weight);
                                }
                                if ($act !== null) {
                                    $act = round($act, 1);
                                }
                            }

                            if ($heightM) {
                                $bmi20 = round(20 * ($heightM ** 2), 1);
                                $bmi25 = round(25 * ($heightM ** 2), 1);
                            }

                            if ($bmi !== null) {
                                if ($bmi >= 40) {
                                    $obesityType = 'Obesidad tipo III';
                                } elseif ($bmi >= 35) {
                                    $obesityType = 'Obesidad tipo II';
                                } elseif ($bmi >= 30) {
                                    $obesityType = 'Obesidad tipo I';
                                } elseif ($bmi >= 25) {
                                    $obesityType = 'Sobrepeso';
                                } else {
                                    $obesityType = 'Normopeso';
                                }
                            }

                            if ($idealCenter !== null) {
                                $scaleMin = max(0, $idealCenter - 5);
                                $scaleMax = $weight !== null && $weight > 0 ? $weight + 10 : ($idealCenter + 5);

                                if ($weight !== null && $weight > 0 && $scaleMax > $scaleMin) {
                                    $currentPosition = max(0, min(100, (($weight - $scaleMin) / ($scaleMax - $scaleMin)) * 100));
                                }

                                if ($scaleMax > $scaleMin) {
                                    $points = 120;
                                    $step = ($scaleMax - $scaleMin) / ($points - 1);
                                    $sigma = max(0.5, 10 / 6);

                                for ($i = 0; $i < $points; $i++) {
                                    $xWeight = round($scaleMin + ($step * $i), 1);
                                    $distance = abs($xWeight - $idealCenter);
                                    $value = round(1.4 * exp(-($distance ** 2) / (2 * ($sigma ** 2))), 3);
                                    $chartIdeal[] = ['x' => $xWeight, 'y' => $value];
                                }

                                $chartSeries = [
                                    ['name' => 'Ideal', 'data' => $chartIdeal],
                                ];

                                if ($idealCenter !== null) {
                                    $bandMin = max(0, $idealCenter - 0.6);
                                    $bandMax = $idealCenter + 0.6;
                                    $chartAnnotations[] = [
                                        'x' => round($bandMin, 1),
                                        'x2' => round($bandMax, 1),
                                        'fillColor' => '#38bdf8',
                                        'opacity' => 0.2,
                                        'borderColor' => 'transparent',
                                    ];
                                    $chartAnnotations[] = [
                                        'x' => round($idealCenter, 1),
                                        'borderColor' => '#38bdf8',
                                        'strokeDashArray' => 0,
                                        'label' => [
                                            'text' => 'Ideal ' . $idealCenter . ' kg',
                                            'style' => [
                                                'color' => '#0f172a',
                                                'background' => '#38bdf8',
                                            ],
                                        ],
                                    ];
                                }

                                if ($weight !== null) {
                                    $labelSuffix = '';
                                    if ($weight < $scaleMin) {
                                        $markerWeight = $scaleMin;
                                        $labelSuffix = ' (por debajo)';
                                    } elseif ($weight > $scaleMax) {
                                        $markerWeight = $scaleMax;
                                        $labelSuffix = ' (por encima)';
                                    } else {
                                        $markerWeight = $weight;
                                    }
                                    $chartAnnotations[] = [
                                        'x' => round($markerWeight, 1),
                                        'borderColor' => '#f59e0b',
                                        'strokeDashArray' => 0,
                                        'label' => [
                                            'text' => 'Actual ' . round($weight, 1) . ' kg' . $labelSuffix,
                                            'style' => [
                                                'color' => '#111827',
                                                'background' => '#f59e0b',
                                            ],
                                        ],
                                    ];
                                }
                            }
                            }
                        }
                    @endphp
                    <div class="md:col-span-2">
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
                                        <span class="h-2.5 w-2.5 rounded-full bg-sky-500"></span>
                                        Ideal
                                    </span>
                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
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
                                        data-chart-colors='@json(["#38bdf8"])'
                                        data-chart-x-annotations='@json($chartAnnotations)'
                                        data-series='@json($chartSeries)'
                                        class="h-[170px]"></div>
                                    <div class="grid grid-cols-4 text-[11px] text-neutral-500 dark:text-neutral-400">
                                        <span class="text-left">{{ $scaleMin }} kg</span>
                                        <span class="text-center">{{ $idealCenter }} kg</span>
                                        <span class="text-center">{{ $weight !== null ? $weight : '—' }} kg</span>
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
                    </div>
                    <div class="md:col-span-2">
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
                            <div class="flex items-center justify-between text-sm border-t border-neutral-200 dark:border-neutral-800 pt-3">
                                <span class="text-neutral-600 dark:text-neutral-300">Clasificación (Obesidad)</span>
                                <span class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $obesityType ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <flux:separator variant="subtle" class="mt-8" />
                <flux:heading size="lg">{{ __('students.health_data_section') }}</flux:heading>
                <div class="space-y-4">
                    <flux:textarea wire:model.defer="data.injuries" :label="__('students.injuries')" rows="2" />
                </div>

                <flux:separator variant="subtle" class="mt-8" />
                <flux:heading size="lg">{{ __('students.communication_section') }}</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <flux:label>{{ __('students.notifications') }}</flux:label>
                        <div class="flex flex-col gap-2 mt-2">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                                <input type="checkbox" wire:model.defer="data.notifications.new_plan"
                                    class="form-checkbox accent-blue-600 dark:accent-blue-400 rounded focus:ring-2 focus:ring-blue-500" />
                                {{ __('students.notification_new_plan') }}
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                                <input type="checkbox" wire:model.defer="data.notifications.session_reminder"
                                    class="form-checkbox accent-blue-600 dark:accent-blue-400 rounded focus:ring-2 focus:ring-blue-500" />
                                {{ __('students.notification_session_reminder') }}
                            </label>
                        </div>
                    </div>
                </div>

                <flux:separator variant="subtle" class="mt-8" />
                <flux:heading size="lg">{{ __('students.extra_data_section') }}</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:input wire:model.defer="data.emergency_contact.name" :label="__('students.emergency_contact_name')" />
                    </div>
                    <div>
                        <flux:input wire:model.defer="data.emergency_contact.phone" :label="__('students.emergency_contact_phone')" />
                    </div>
                </div>

                {{-- Footer --}}
                <div class="pt-6 max-w-3xl">
                    <div class="flex justify-end gap-3 items-center text-sm opacity-80">
                        <flux:checkbox size="sm" label="{{ __('site.back_list') }}" wire:model.live="back" />
                        <flux:button as="a" variant="ghost" href="{{ route('tenant.dashboard.students.index') }}" size="sm">
                            {{ __('site.back') }}
                        </flux:button>
                        <flux:button type="submit" size="sm">
                            {{ $editMode ? __('students.update_button') : __('students.create_button') }}
                        </flux:button>
                    </div>
                </div>

                <flux:separator variant="subtle" class="mt-8" />
            </div>
        </form>

        @if ($editMode && $student)
            <flux:modal name="assign-plan-drawer" variant="flyout" class="max-w-lg">
                <div class="space-y-2 mb-6">
                    <flux:heading size="lg">{{ __('students.assign_plan_modal_title') }}</flux:heading>
                    <flux:subheading>{{ __('students.assign_plan_modal_description') }}</flux:subheading>
                </div>
                <livewire:tenant.students.assign-plan :student="$student" :key="'assign-' . $student->uuid" />
            </flux:modal>
        @endif
    </div>
</div>
