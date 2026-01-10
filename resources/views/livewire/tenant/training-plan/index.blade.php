<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6 space-y-6">

        {{-- 🔹 Header --}}
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('training_plans.index_title') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">{{ __('training_plans.index_subheading') }}
                    </flux:subheading>
                </div>

                <flux:button as="a" href="{{ route('tenant.dashboard.training-plans.create') }}" variant="primary"
                    icon="plus">
                    {{ __('training_plans.new_training_plan') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>

        {{-- 🔹 Tabla principal --}}
        <section class="w-full">
            <x-data-table :pagination="$plans">

                {{-- Filtros --}}
                <x-slot name="filters">
                    <x-index-filters :searchPlaceholder="__('training_plans.search_placeholder')">
                        <x-slot name="additionalFilters">
                            {{-- Filtro por status --}}
                            <div class="min-w-[160px]">
                                <flux:select size="sm" wire:model.live="status" :label="__('common.status')">
                                    <option value="">{{ __('common.all') }}</option>
                                    <option value="1">{{ __('common.active') }}</option>
                                    <option value="0">{{ __('common.inactive') }}</option>
                                </flux:select>
                            </div>
                        </x-slot>
                    </x-index-filters>
                </x-slot>

                {{-- Encabezado --}}
                <x-slot name="head">
                    <th
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-center w-16">
                        {{ __('training_plans.image') }}
                    </th>
                    <th wire:click="sort('name')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        <span class="inline-flex items-center gap-1">
                            {{ __('training_plans.name') }}
                            @if ($sortBy === 'name')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('training_plans.goal') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-center">
                        {{ __('training_plans.exercises') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('training_plans.duration') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('common.status') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
                        {{ __('common.actions') }}
                    </th>
                </x-slot>

                {{-- Filas --}}
                @forelse ($plans as $plan)
                    @php
                        // Obtener imágenes desde exercises usando el accessor
                        $planExercises = $plan->exercises; // Collection de exercises_data con nombres
                        $exerciseIds = collect($plan->exercises_data ?? [])->pluck('exercise_id')->toArray();
                        $exerciseModels = !empty($exerciseIds) ? \App\Models\Tenant\Exercise::whereIn('id', $exerciseIds)->get() : collect([]);

                        $images = $exerciseModels
                            ->map(fn($e) => $e->getFirstMediaUrl('images', 'thumb'))
                            ->filter(fn($url) => !empty($url))
                            ->unique()
                            ->take(4)
                            ->values();

                        $hasImages = $images->isNotEmpty();
                        $initial = strtoupper(substr($plan->name, 0, 1));
                        $countExercises = count($plan->exercises_data ?? []);
                    @endphp

                    <tr wire:key="plan-{{ $plan->uuid }}" class="divide-y divide-gray-200 dark:divide-neutral-700">
                        {{-- Imagen --}}
                        <td class="align-middle px-6 py-4">
                            <div class="flex justify-start items-center h-full">
                                @if ($hasImages)
                                    <div
                                        class="grid grid-cols-2 w-10 h-10 overflow-hidden rounded-sm border border-gray-300 dark:border-neutral-700 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-neutral-800 dark:to-neutral-700">
                                        @foreach (range(0, 3) as $i)
                                            @if (isset($images[$i]))
                                                <div class="aspect-square w-full h-full">
                                                    <img src="{{ $images[$i] }}"
                                                        class="w-full h-full object-cover" />
                                                </div>
                                            @else
                                                <div
                                                    class="aspect-square w-full h-full bg-gray-100/60 dark:bg-neutral-700/40">
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div
                                        class="w-10 h-10 flex items-center justify-center rounded-sm border border-gray-300 dark:border-neutral-700 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-neutral-800 dark:to-neutral-700 text-[11px] font-semibold text-gray-600 dark:text-neutral-400">
                                        {{ $initial }}
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- Nombre --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $plan->name }}

                            @if ($plan->student_id)
                            <div class="text-gray-600 dark:text-neutral-400">
                                {{ $plan->student?->full_name }}
                            </div>
                            @endif
                        </td>

                        {{-- Objetivo --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $plan->goal ?? '—' }}
                        </td>

                        {{-- Ejercicios --}}
                        <td class="align-top px-6 py-4 text-sm text-center text-gray-700 dark:text-neutral-300">
                            {{ $countExercises > 0 ? $countExercises : '—' }}
                        </td>

                        {{-- Duración --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $plan->duration ?: '—' }}
                        </td>

                        {{-- Estado --}}
                        <td class="align-top px-6 py-4 text-sm">
                            @php
                                $state = $plan->is_active ? 'active' : 'inactive';
                                $styles = [
                                    'active' =>
                                        'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-950/40 dark:text-green-300 dark:ring-green-900',
                                    'inactive' =>
                                        'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800',
                                ];
                            @endphp
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $styles[$state] }}">
                                {{ __('common.' . $state) }}
                            </span>
                        </td>

                        {{-- Acciones --}}
                        <td class="align-top px-6 py-4 text-end text-sm font-medium">
                            <span
                                class="inline-flex items-center gap-2 space-x-1 text-xs text-gray-400 dark:text-neutral-500 whitespace-nowrap">


                                <flux:modal.trigger name="assign-plan">
                                    <flux:button size="sm" wire:click="prepareAssign('{{ $plan->uuid }}')">
                                        {{ __('common.assign') }}
                                    </flux:button>
                                </flux:modal.trigger>

                                <flux:button size="sm" wire:click="clone('{{ $plan->uuid }}')">
                                    {{ __('common.duplicate') }}
                                </flux:button>

                                <flux:button size="sm" as="a" wire:navigate
                                    href="{{ route('tenant.dashboard.training-plans.edit', $plan->uuid) }}">
                                    {{ __('common.edit') }}
                                </flux:button>

                                <flux:modal.trigger name="confirm-delete-training-plan">
                                    <flux:button size="sm" variant="ghost"
                                        wire:click="confirmDelete('{{ $plan->uuid }}')">
                                        {{ __('common.delete') }}
                                    </flux:button>
                                </flux:modal.trigger>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                            {{ __('common.empty_state') }}
                        </td>
                    </tr>
                @endforelse

                {{-- Modal global dentro del slot --}}
                <x-slot name="modal">
                    <flux:modal name="confirm-delete-training-plan" class="min-w-[22rem]" x-data
                        @plan-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-training-plan' })">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __('common.delete_title') }}</flux:heading>
                                <flux:text class="mt-2">{{ __('common.delete_msg') }}</flux:text>
                            </div>
                            <div class="flex gap-2">
                                <flux:spacer />
                                <flux:modal.close>
                                    <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                                </flux:modal.close>
                                <flux:button wire:click="delete" variant="danger">
                                    {{ __('common.confirm_delete') }}
                                </flux:button>
                            </div>
                        </div>
                    </flux:modal>
                </x-slot>

                <flux:modal name="assign-plan" class="min-w-[28rem]" x-data
                    @plan-assigned.window="$dispatch('modal-close', { name: 'assign-plan' })">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">{{ __('training_plans.assign_title') }}</flux:heading>
                            <flux:text class="mt-2">{{ __('training_plans.assign_subheading') }}</flux:text>
                        </div>

                        {{-- Buscador de alumno --}}
                        <div class="space-y-3">
                            <flux:input size="sm" wire:model.live.debounce.300ms="studentSearch"
                                :label="__('common.search_student')"
                                placeholder="{{ __('common.type_student_name') }}" />

                            {{-- Selector dinámico --}}
                            @if (!empty($students))
                                <flux:select size="sm" wire:model.live="selectedStudentUuid"
                                    :label="__('common.select_student')">
                                    <option value="">{{ __('common.choose_option') }}</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student['uuid'] }}">
                                            {{ $student['full_name'] }} — {{ $student['email'] }}
                                        </option>
                                    @endforeach
                                </flux:select>
                            @endif
                        </div>

                        {{-- Fechas opcionales de vigencia --}}
                        <div class="grid grid-cols-2 gap-3">
                            <flux:input type="date" size="sm" wire:model="assignedFrom"
                                :label="__('training_plans.assigned_from')" />
                            <flux:input type="date" size="sm" wire:model="assignedUntil"
                                :label="__('training_plans.assigned_until')" />
                        </div>

                        {{-- Acciones --}}
                        <div class="flex gap-2">
                            <flux:spacer />
                            <flux:modal.close>
                                <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                            </flux:modal.close>
                            <flux:button wire:click="assignToStudent" variant="primary"
                                :disabled="!$selectedStudentUuid">
                                {{ __('common.assign') }}
                            </flux:button>
                        </div>
                    </div>
                </flux:modal>



            </x-data-table>
        </section>
    </div>
</div>
