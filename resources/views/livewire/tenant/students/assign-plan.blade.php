<div class="space-y-6">
    {{-- Plan vigente actual --}}
    @if ($currentPlanInfo)
        <div class="rounded-lg p-4 border" style="background-color: var(--ftt-color-base-transparent); border-color: var(--ftt-color-base);">
            <h4 class="font-semibold text-gray-900 dark:text-neutral-100 mb-1">
                Plan vigente actual
            </h4>
            <p class="text-sm text-gray-700 dark:text-neutral-300">
                {{ $currentPlanInfo }}
            </p>
        </div>
    @else
        <div class="bg-gray-50 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-lg p-4">
            <p class="text-sm text-gray-600 dark:text-neutral-400">
                Este alumno no tiene un plan vigente
            </p>
        </div>
    @endif

    {{-- Plan futuro pendiente --}}
    @if ($hasFuturePlan)
        <div class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <h4 class="font-semibold text-amber-900 dark:text-amber-100 mb-1">
                        Plan futuro pendiente
                    </h4>
                    <p class="text-sm text-amber-800 dark:text-amber-200 mb-2">
                        {{ $futurePlanInfo }}
                    </p>
                    <p class="text-xs text-amber-700 dark:text-amber-300">
                        ⚠️ Al asignar un nuevo plan, este plan futuro será dado de baja automáticamente.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Formulario de asignación --}}
    <form wire:submit.prevent="assign" class="space-y-4">
        <div>
            <flux:label>{{ __('students.training_plan') }}</flux:label>

            @if ($selectedPlan)
                {{-- Plan seleccionado --}}
                <div class="mt-2 flex items-center justify-between bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-sm font-medium text-green-900 dark:text-green-100">
                            {{ $selectedPlan['name'] }}
                        </span>
                    </div>
                    <flux:button
                        type="button"
                        variant="ghost"
                        size="sm"
                        wire:click="clearSelection"
                        icon="x"
                    >
                    </flux:button>
                </div>
            @else
                {{-- Búsqueda de plan --}}
                <div class="mt-2 relative">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Buscar plan de entrenamiento..."
                        icon="search"
                    />

                    {{-- Feedback mientras se selecciona un plan --}}
                    <div wire:loading.flex wire:target="selectPlan" class="absolute inset-0 z-20 bg-white/70 dark:bg-neutral-900/70 backdrop-blur-sm rounded-lg items-center justify-center gap-2 text-sm font-medium text-gray-700 dark:text-neutral-200">
                        <x-icons.lucide.loader class="h-4 w-4 animate-spin" />
                        <span>Asignando plan...</span>
                    </div>

                    @if (!empty($search) || count($plans) > 0)
                        <div class="absolute z-10 mt-1 w-full bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-lg shadow-lg max-h-60 overflow-auto">
                            @forelse ($plans as $plan)
                                <button
                                    type="button"
                                    wire:click="selectPlan({{ $plan['id'] }})"
                                    class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-neutral-700/50 border-b border-gray-100 dark:border-neutral-700 last:border-b-0 transition-colors"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $plan['name'] }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-neutral-400">
                                            {{ $plan['date'] }}
                                        </span>
                                    </div>
                                </button>
                            @empty
                                <div class="px-4 py-3 text-sm text-gray-500 dark:text-neutral-400 text-center">
                                    @if (!empty($search))
                                        No se encontraron planes con "{{ $search }}"
                                    @else
                                        No hay planes disponibles
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>

                @if (empty($search) && count($plans) > 0)
                    <p class="mt-1 text-xs text-gray-500 dark:text-neutral-400">
                        Mostrando los 5 planes más recientes. Usa la búsqueda para encontrar otros.
                    </p>
                @endif
            @endif

            @error('training_plan_id')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        @if ($currentPlan && $currentPlan->is_active && $currentPlan->ends_at && !$currentPlan->ends_at->isPast())
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
                <flux:checkbox
                    wire:model.live="startNow"
                    label="Empezar ya (desactivar plan actual)"
                    description="Si marcas esta opción, el plan actual se dará de baja y el nuevo comenzará hoy. Si no, el nuevo plan quedará encolado. Nota: Los planes futuros pendientes se darán de baja automáticamente en ambos casos."
                />
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <flux:input
                    wire:model="starts_at"
                    type="date"
                    :label="__('students.starts_at')"
                />
            </div>
            <div>
                <flux:input
                    wire:model="ends_at"
                    type="date"
                    :label="__('students.ends_at')"
                />
            </div>
        </div>

        <div class="flex gap-3 justify-end pt-4">
            <flux:modal.close>
                <flux:button type="button" variant="ghost">
                    {{ __('common.cancel') }}
                </flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">
                {{ __('students.assign_plan') }}
            </flux:button>
        </div>
    </form>
</div>
