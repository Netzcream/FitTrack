<div class="space-y-6">
    <flux:heading size="lg">{{ __('Paso 4 — Confirmar asignación') }}</flux:heading>
    <flux:subheading>
        {{ __('Revisá los datos antes de continuar. Podés asignar directamente o personalizar el plan antes.') }}
    </flux:subheading>

    <!-- Card resumen -->
    <div
        class="flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl p-5 md:p-6 dark:bg-neutral-900 dark:border-neutral-700 dark:shadow-neutral-700/70">
        <h3 class="text-base font-semibold text-gray-800 dark:text-neutral-100 mb-3">
            {{ __('Resumen de la asignación') }}
        </h3>

        <dl class="divide-y divide-gray-200 dark:divide-neutral-700">
            <div class="flex items-start justify-between py-3">
                <dt class="text-sm font-medium text-gray-500 dark:text-neutral-400">
                    {{ __('Plantilla') }}
                </dt>
                <dd class="text-sm text-gray-800 dark:text-neutral-200 text-end">
                    {{ $template->name }} <span
                        class="text-gray-500 dark:text-neutral-500">({{ $template->code }})</span>
                </dd>
            </div>

            <div class="flex items-start justify-between py-3">
                <dt class="text-sm font-medium text-gray-500 dark:text-neutral-400">
                    {{ __('Alumnos') }}
                </dt>
                <dd class="text-sm text-gray-800 dark:text-neutral-200 text-end">
                    {{ $students->pluck('full_name')->join(', ') }}
                </dd>
            </div>

            <div class="flex items-start justify-between py-3">
                <dt class="text-sm font-medium text-gray-500 dark:text-neutral-400">
                    {{ __('Fecha de inicio') }}
                </dt>
                <dd class="text-sm text-gray-800 dark:text-neutral-200 text-end">
                    {{ \Carbon\Carbon::parse($state['start_date'])->format('d/m/Y') }}
                </dd>
            </div>

            @if (!empty($state['name_override']))
                <div class="flex items-start justify-between py-3">
                    <dt class="text-sm font-medium text-gray-500 dark:text-neutral-400">
                        {{ __('Nombre del plan') }}
                    </dt>
                    <dd class="text-sm text-gray-800 dark:text-neutral-200 text-end">
                        {{ $state['name_override'] }}
                    </dd>
                </div>
            @endif
        </dl>
    </div>

    <!-- Botones de acción -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-3 mt-6">
        <flux:button variant="subtle" wire:click="back" class="order-2 sm:order-1">
            ← {{ __('Volver') }}
        </flux:button>

        <div class="flex gap-2 order-1 sm:order-2">
            @if (count($students) < 2)
                <flux:button variant="outline" wire:click="cloneAndEdit">
                    {{ __('Personalizar antes de asignar') }}
                </flux:button>
            @endif

            <flux:button variant="primary" wire:click="confirm">
                {{ __('Asignar ahora') }}
            </flux:button>
        </div>
    </div>
</div>
