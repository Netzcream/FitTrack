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
                                    @if ($plan->status->value === 'active' && !$isExpired)
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
                        <flux:select wire:model.defer="billing_frequency" :label="__('students.billing_frequency')">
                            <option value="monthly">{{ __('students.monthly') }}</option>
                            <option value="quarterly">{{ __('students.quarterly') }}</option>
                            <option value="yearly">{{ __('students.yearly') }}</option>
                        </flux:select>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-4 rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/50">
                    <flux:checkbox wire:model.defer="is_user_enabled" size="sm" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-neutral-100">{{ __('students.user_enabled') }}</div>
                        <div class="text-xs text-gray-600 dark:text-neutral-400">Permite al alumno acceder a su perfil y planes</div>
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
                        <flux:input type="text" wire:model="lastWeightDisplay" :label="__('students.weight_kg')" disabled />
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">El peso se registra en la sección de Evolución de Peso</p>
                    </div>

                    @php
                        $height = $data['height_cm'] ?? null;
                        $weight = $lastRecordedWeight ?? $data['weight_kg'] ?? null;

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
                                        'fillColor' => tenant_config('color_base', '#263d83'),
                                        'opacity' => 0.2,
                                        'borderColor' => 'transparent',
                                    ];
                                    $chartAnnotations[] = [
                                        'x' => round($idealCenter, 1),
                                        'borderColor' => tenant_config('color_base', '#263d83'),
                                        'strokeDashArray' => 0,
                                        'label' => [
                                            'text' => 'Ideal ' . $idealCenter . ' kg',
                                            'style' => [
                                                'color' => '#ffffff',
                                                'background' => tenant_config('color_base', '#263d83'),
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
                                        'borderColor' => '#9ca3af',
                                        'strokeDashArray' => 0,
                                        'label' => [
                                            'text' => 'Actual ' . round($weight, 1) . ' kg' . $labelSuffix,
                                            'style' => [
                                                'color' => '#ffffff',
                                                'background' => '#9ca3af',
                                            ],
                                        ],
                                    ];
                                }
                            }
                            }
                        }
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                {{-- Evolución de Peso --}}
                @include('livewire.tenant.students.partials.weight-evolution')

                {{-- Curva Peso Ideal --}}
                @include('livewire.tenant.students.partials.ideal-weight-curve')

                {{-- Cálculos Antropométricos --}}
                @include('livewire.tenant.students.partials.anthropometric-calculations')

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
            {{-- Drawer para asignar plan --}}
            <flux:modal name="assign-plan-drawer" variant="flyout" class="max-w-lg">
                <div class="space-y-2 mb-6">
                    <flux:heading size="lg">{{ __('students.assign_plan_modal_title') }}</flux:heading>
                    <flux:subheading>{{ __('students.assign_plan_modal_description') }}</flux:subheading>
                </div>
                <livewire:tenant.students.assign-plan :student="$student" :key="'assign-' . $student->uuid" />
            </flux:modal>

            {{-- Drawer para agregar peso --}}
            <flux:modal name="add-weight-drawer" variant="flyout" class="max-w-md">
                <div class="space-y-2 mb-6">
                    <flux:heading size="lg">Registrar peso</flux:heading>
                    <flux:subheading>Agrega un nuevo registro de peso para el estudiante</flux:subheading>
                </div>

                <div class="space-y-4">
                    {{-- Peso actual --}}
                    @if($lastWeightDisplay && $lastWeightDisplay !== '—')
                        <div class="rounded-lg p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800">
                            <div class="flex items-center gap-2 mb-1">
                                <x-icons.lucide.weight class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                                <span class="text-xs font-medium text-purple-700 dark:text-purple-300">Peso actual</span>
                            </div>
                            <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ $lastWeightDisplay }}</p>
                        </div>
                    @endif

                    {{-- Formulario --}}
                    <div class="space-y-4">
                        <flux:input type="number"
                                  step="0.1"
                                  wire:model.defer="newWeight"
                                  label="Peso (kg)"
                                  placeholder="75.5"
                                  required />

                        <flux:input type="date"
                                  wire:model.defer="newWeightDate"
                                  label="Fecha"
                                  required />

                        <flux:textarea wire:model.defer="newWeightNotes"
                                     label="Notas (opcional)"
                                     placeholder="Ej: Medición matinal en ayunas"
                                     rows="3" />
                    </div>

                    {{-- Historial reciente --}}
                    @if(count($weightHistory) > 0)
                        <div class="pt-4 border-t border-gray-200 dark:border-neutral-700">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-neutral-300">Últimos registros</h4>
                                <span class="text-xs text-gray-500 dark:text-neutral-400 bg-gray-100 dark:bg-neutral-800 px-2 py-0.5 rounded-full">
                                    {{ count($weightHistory) }} registro{{ count($weightHistory) > 1 ? 's' : '' }}
                                </span>
                            </div>
                            <div class="space-y-2 max-h-80 overflow-y-auto pr-1 custom-scrollbar">
                                @foreach($weightHistory as $index => $entry)
                                    <div class="group relative flex items-center gap-3 p-3 bg-gradient-to-r from-gray-50 to-white dark:from-neutral-800 dark:to-neutral-800/50 rounded-lg border border-gray-200 dark:border-neutral-700 hover:border-purple-300 dark:hover:border-purple-700 hover:shadow-sm transition-all">
                                        {{-- Indicador visual --}}
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                                @if($index === 0)
                                                    <x-icons.lucide.trending-down class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                                @else
                                                    <x-icons.lucide.weight class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Contenido --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-baseline gap-2">
                                                <p class="text-lg font-bold text-gray-900 dark:text-neutral-100">{{ $entry['weight'] }} kg</p>
                                                @if($index === 0)
                                                    <span class="text-xs font-medium text-purple-600 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/30 px-1.5 py-0.5 rounded">
                                                        Actual
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <x-icons.lucide.calendar class="w-3 h-3 text-gray-400 dark:text-neutral-500" />
                                                <p class="text-xs text-gray-500 dark:text-neutral-400">{{ $entry['date'] }}</p>
                                            </div>
                                            @if($entry['notes'])
                                                <p class="text-xs text-gray-600 dark:text-neutral-400 mt-1 italic line-clamp-1">
                                                    "{{ $entry['notes'] }}"
                                                </p>
                                            @endif
                                        </div>

                                        {{-- Botón eliminar --}}
                                        <button type="button"
                                                wire:click="deleteWeightEntry('{{ $entry['id'] }}')"
                                                wire:confirm="¿Eliminar este registro?"
                                                class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 hover:scale-110 transition-all opacity-0 group-hover:opacity-100"
                                                title="Eliminar registro">
                                            <x-icons.lucide.trash-2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Estilos para scrollbar personalizado --}}
                        <style>
                            .custom-scrollbar::-webkit-scrollbar {
                                width: 6px;
                            }
                            .custom-scrollbar::-webkit-scrollbar-track {
                                background: transparent;
                            }
                            .custom-scrollbar::-webkit-scrollbar-thumb {
                                background: #d1d5db;
                                border-radius: 3px;
                            }
                            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                                background: #9ca3af;
                            }
                            .dark .custom-scrollbar::-webkit-scrollbar-thumb {
                                background: #4b5563;
                            }
                            .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                                background: #6b7280;
                            }
                        </style>
                    @endif

                    {{-- Botones de acción --}}
                    <div class="flex gap-3 pt-4">
                        <flux:button variant="ghost" class="flex-1" onclick="Flux.modal('add-weight-drawer').close()">
                            Cancelar
                        </flux:button>
                        <flux:button wire:click="addWeightEntry" class="flex-1">
                            Registrar
                        </flux:button>
                    </div>
                </div>
            </flux:modal>

            {{-- Script para cerrar drawer automáticamente --}}
            <script>
                document.addEventListener('livewire:init', () => {
                    Livewire.on('weight-added', () => {
                        // Cerrar el drawer automáticamente
                        const modal = Flux.modal('add-weight-drawer');
                        if (modal) {
                            modal.close();
                        }
                    });
                });
            </script>
        @endif
    </div>
</div>
