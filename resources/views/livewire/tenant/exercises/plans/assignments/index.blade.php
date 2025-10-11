<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <flux:heading size="xl">{{ __('Asignaciones de rutinas') }}</flux:heading>
            <flux:subheading>{{ __('Revisá las rutinas activas y su historial de asignaciones.') }}</flux:subheading>
        </div>
        <flux:button variant="primary" href="{{ route('tenant.dashboard.exercises.plans.assign.wizard') }}">
            + {{ __('Nueva asignación') }}
        </flux:button>
    </div>
    <flux:separator variant="subtle" />

    <!-- Filtros -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div class="md:col-span-2">
            <flux:input wire:model.live.debounce.400ms="q" label="{{ __('Buscar') }}"
                placeholder="{{ __('Alumno o nombre del plan') }}" />
        </div>
        <div>
            <flux:select wire:model.live="status" label="{{ __('Estado') }}">
                <option value="">{{ __('Todos') }}</option>
                <option value="active">{{ __('Activo') }}</option>
                <option value="finished">{{ __('Finalizado') }}</option>
                <option value="pending">{{ __('Pendiente') }}</option>
            </flux:select>
        </div>
        <div>
            <flux:input type="date" wire:model.live.debounce.400ms="date_from" label="{{ __('Desde') }}" />
        </div>
        <div>
            <flux:input type="date" wire:model.live.debounce.400ms="date_to" label="{{ __('Hasta') }}" />
        </div>
    </div>

    <!-- Tabla -->
    <div class="flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl overflow-hidden dark:bg-neutral-900 dark:border-neutral-700 dark:shadow-neutral-700/70">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                    <thead class="bg-gray-50 dark:bg-neutral-800/50">
                        <tr>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                {{ __('Alumno') }}
                            </th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                {{ __('Plan') }}
                            </th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                {{ __('Inicio') }}
                            </th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                {{ __('Estado') }}
                            </th>
                            <th class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                {{ __('Acciones') }}
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                        @forelse ($assignments as $a)
                            @php
                                $status = $a->status;
                                $statusMap = [
                                    'active' => ['label' => __('Activo'), 'color' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'],
                                    'finished' => ['label' => __('Finalizado'), 'color' => 'bg-gray-200 text-gray-700 dark:bg-neutral-800 dark:text-neutral-400'],
                                    'pending' => ['label' => __('Pendiente'), 'color' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'],
                                ];
                                $statusColor = $statusMap[$status]['color'] ?? 'bg-gray-100 text-gray-600';
                                $statusLabel = $statusMap[$status]['label'] ?? ucfirst($status);
                            @endphp

                            <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200">
                                    {{ $a->student->full_name ?? ($a->student->first_name.' '.$a->student->last_name) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                                    {{ $a->plan->name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-neutral-400">
                                    {{ \Carbon\Carbon::parse($a->start_date)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-end">
                                    <div class="flex justify-end gap-2">
                                        <flux:button size="xs" variant="outline"
                                            href="{{ route('tenant.dashboard.exercises.plans.builder', ['plan' => $a->plan->id]) }}">
                                            {{ __('Ver plan') }}
                                        </flux:button>
                                        @if($status === 'active')
                                            <flux:button size="xs" variant="ghost" wire:click="finish({{ $a->id }})"
                                                wire:loading.attr="disabled">
                                                {{ __('Finalizar') }}
                                            </flux:button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-neutral-400">
                                    {{ __('No hay asignaciones registradas.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <div>
        {{ $assignments->links('components.preline.pagination') }}
    </div>
</div>
