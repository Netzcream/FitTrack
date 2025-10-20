<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-6">
            {{-- Header sticky --}}
            <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
                <div class="flex items-center justify-between gap-4 max-w-3xl">
                    <div>
                        <flux:heading size="xl" level="1">
                            {{ $editMode ? __('training_plans.edit_title') : __('training_plans.new_title') }}
                        </flux:heading>

                        <flux:subheading size="lg" class="mb-6">
                            @if ($student_id && $plan?->student)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-md border border-blue-200 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-950/40 text-sm text-blue-700 dark:text-blue-300 font-medium">
                                    {{ __('training_plans.assigned_to') }}:
                                    <span class="ml-1 font-semibold">{{ $plan->student->full_name }}</span>
                                </span>
                            @else
                                {{ $editMode ? __('training_plans.edit_subheading') : __('training_plans.new_subheading') }}
                            @endif
                        </flux:subheading>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-tenant.action-message on="saved">
                            {{ __('site.saved') }}
                        </x-tenant.action-message>

                        <flux:checkbox size="sm" label="{{ __('site.back_list') }}" wire:model.live="back" />

                        @if ($student_id && $plan?->student)
                            {{-- Volver a los planes del alumno --}}
                            <flux:button as="a" variant="ghost" size="sm"
                                href="{{ route('tenant.dashboard.students.training-plans', $plan->student->uuid) }}">
                                {{ __('site.back') }}
                            </flux:button>
                        @else
                            {{-- Volver al listado general --}}
                            <flux:button as="a" variant="ghost" size="sm"
                                href="{{ route('tenant.dashboard.training-plans.index') }}">
                                {{ __('site.back') }}
                            </flux:button>
                        @endif

                        <flux:button type="submit" size="sm">
                            {{ $editMode ? __('training_plans.update_button') : __('training_plans.create_button') }}
                        </flux:button>
                    </div>
                </div>
                <flux:separator variant="subtle" class="mt-2" />
            </div>

            {{-- Contenido principal --}}
            <div class="max-w-3xl space-y-4 pt-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:input wire:model.defer="name" label="{{ __('training_plans.name') }}" required
                            autocomplete="off" />
                    </div>
                    <div>
                        <flux:input wire:model.defer="goal" label="{{ __('training_plans.goal') }}"
                            autocomplete="off" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input wire:model.defer="duration" label="{{ __('training_plans.duration') }}"
                        placeholder="2 semanas" autocomplete="off" />
                    <div class="flex items-center gap-2 pt-5">
                        <flux:checkbox wire:model.defer="is_active" size="sm" :label="__('training_plans.is_active')" />

                    </div>
                </div>

                {{-- Fechas: solo si pertenece a un alumno --}}
                @if ($student_id)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <flux:input type="date" wire:model.defer="assigned_from"
                                label="{{ __('training_plans.assigned_from') }}" />
                        </div>
                        <div>
                            <flux:input type="date" wire:model.defer="assigned_until"
                                label="{{ __('training_plans.assigned_until') }}" />
                        </div>
                    </div>
                @endif

                <div>
                    <flux:label class="text-xs">{{ __('training_plans.description') }}</flux:label>
                    <flux:textarea wire:model.defer="description" rows="4"
                        placeholder="{{ __('training_plans.description_placeholder') }}" />
                </div>

                {{-- Sección de ejercicios --}}
                <flux:separator variant="subtle" class="my-6" />
                <flux:heading size="md" level="2">{{ __('training_plans.exercises_section') }}</flux:heading>

                <div class="space-y-4">
                    {{-- Buscador --}}
                    <div class="relative">
                        <flux:input size="sm" wire:model.live.debounce.300ms="exerciseSearch"
                            wire:blur="clearSearch" placeholder="{{ __('training_plans.search_exercise') }}" />

                        @if (!empty($availableExercises))
                            <div
                                class="absolute z-20 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-md mt-1 shadow-sm w-full max-h-64 overflow-auto">
                                @foreach ($availableExercises as $ex)
                                    <button type="button" wire:click="addExercise({{ $ex['id'] }})"
                                        class="flex items-center gap-3 w-full text-left px-3 py-2 hover:bg-gray-50 dark:hover:bg-neutral-800 text-sm transition">
                                        @php
                                            $exercise = \App\Models\Tenant\Exercise::find($ex['id']);
                                            $thumb = $exercise?->getFirstMediaUrl('images', 'thumb');
                                        @endphp

                                        @if ($thumb)
                                            <img src="{{ $thumb }}"
                                                class="h-8 w-8 rounded object-cover border border-gray-200 dark:border-neutral-700" />
                                        @else
                                            <div
                                                class="h-8 w-8 flex items-center justify-center rounded border border-gray-300 dark:border-neutral-700 bg-gray-100 dark:bg-neutral-800 text-[10px] font-medium text-gray-500 dark:text-neutral-400">
                                                {{ strtoupper(substr($ex['name'], 0, 2)) }}
                                            </div>
                                        @endif

                                        <div class="flex-1 text-left truncate">
                                            <div class="font-medium text-gray-900 dark:text-neutral-100 leading-tight">
                                                {{ $ex['name'] }}
                                            </div>
                                            <div class="text-[12px] text-gray-600 dark:text-neutral-400">
                                                {{ $ex['category'] ?? __('training_plans.uncategorized') }}
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Lista de ejercicios seleccionados --}}
                    @if ($selectedExercises)
                        <div class="overflow-hidden border border-gray-200 dark:border-neutral-700 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-xs font-medium uppercase text-left w-16">
                                            {{ __('training_plans.image') }}
                                        </th>
                                        <th class="px-4 py-2 text-xs font-medium uppercase text-center w-12">
                                            {{ __('common.order') }}
                                        </th>
                                        <th class="px-4 py-2 text-xs font-medium uppercase text-center w-24">
                                            {{ __('training_plans.day') }}
                                        </th>
                                        <th class="px-4 py-2 text-xs font-medium uppercase text-left w-28">
                                            {{ __('training_plans.prescription') }}
                                        </th>
                                        <th class="px-4 py-2 text-xs font-medium uppercase text-left">
                                            {{ __('training_plans.notes') }}
                                        </th>
                                        <th class="px-4 py-2 text-xs font-medium uppercase text-center w-20">
                                            {{ __('common.actions') }}
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($selectedExercises as $i => $ex)
                                        <tr class="align-middle">
                                            <td rowspan="2" class="px-4 py-2 align-middle text-left">
                                                <div class="flex items-center h-full">
                                                    @if ($ex['image'])
                                                        <img src="{{ $ex['image'] }}"
                                                            class="h-10 w-10 rounded object-cover border border-gray-200 dark:border-neutral-700" />
                                                    @else
                                                        <div
                                                            class="h-10 w-10 flex items-center justify-center rounded border border-gray-300 dark:border-neutral-700 bg-gray-100 dark:bg-neutral-800 text-[11px] font-medium text-gray-500 dark:text-neutral-400">
                                                            {{ strtoupper(substr($ex['name'], 0, 2)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>

                                            <td rowspan="2" class="px-2 py-2 text-center align-middle">
                                                <div
                                                    class="flex flex-col items-center justify-center gap-1 leading-none">
                                                    <a wire:click.prevent="moveUp({{ $i }})"
                                                        title="{{ __('common.move_up') }}"
                                                        class="cursor-pointer text-gray-400 hover:text-gray-600 dark:text-neutral-500 dark:hover:text-neutral-300">
                                                        <x-icons.lucide.chevron-up class="h-4 w-4" />
                                                    </a>
                                                    <a wire:click.prevent="moveDown({{ $i }})"
                                                        title="{{ __('common.move_down') }}"
                                                        class="cursor-pointer text-gray-400 hover:text-gray-600 dark:text-neutral-500 dark:hover:text-neutral-300">
                                                        <x-icons.lucide.chevron-down class="h-4 w-4" />
                                                    </a>
                                                </div>
                                            </td>

                                            <td colspan="3"
                                                class="px-4 pt-1 pb-2 text-[15px] font-medium text-gray-900 dark:text-neutral-100 leading-snug align-bottom">
                                                {{ $ex['name'] }}
                                                <span class="text-[12px] text-gray-700 dark:text-neutral-300">
                                                    ({{ $ex['category'] }})
                                                </span>
                                            </td>

                                            <td rowspan="2" class="px-3 py-3 text-center text-xs align-bottom">
                                                <div class="flex flex-col justify-end items-center h-full">
                                                    <flux:button variant="ghost" size="sm"
                                                        wire:click="removeExercise({{ $i }})">
                                                        {{ __('common.delete') }}
                                                    </flux:button>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="align-middle border-b border-gray-200 dark:border-neutral-700">
                                            <td class="px-3 pt-0 pb-3 text-sm w-24">
                                                <flux:select
                                                    wire:model.defer="selectedExercises.{{ $i }}.day"
                                                    size="sm">
                                                    @for ($d = 1; $d <= 7; $d++)
                                                        <option value="{{ $d }}">{{ $d }}
                                                        </option>
                                                    @endfor
                                                </flux:select>
                                            </td>

                                            <td class="px-3 pt-0 pb-3 text-sm w-44">
                                                <flux:input
                                                    wire:model.defer="selectedExercises.{{ $i }}.detail"
                                                    size="sm" placeholder="4x10" />
                                            </td>

                                            <td class="px-3 pt-0 pb-3 text-sm">
                                                <flux:input
                                                    wire:model.defer="selectedExercises.{{ $i }}.notes"
                                                    size="sm" placeholder="RPE 7" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Barra inferior compacta --}}
                <div class="pt-6 max-w-3xl">
                    <div class="flex justify-end gap-3 items-center text-sm opacity-80">
                        <x-tenant.action-message on="saved">
                            {{ __('site.saved') }}
                        </x-tenant.action-message>

                        <flux:checkbox size="sm" label="{{ __('site.back_list') }}" wire:model.live="back" />

                        @if ($student_id && $plan?->student)
                            <flux:button as="a" variant="ghost" size="sm"
                                href="{{ route('tenant.dashboard.students.training-plans', $plan->student->uuid) }}">
                                {{ __('site.back') }}
                            </flux:button>
                        @else
                            <flux:button as="a" variant="ghost" size="sm"
                                href="{{ route('tenant.dashboard.training-plans.index') }}">
                                {{ __('site.back') }}
                            </flux:button>
                        @endif

                        <flux:button type="submit" size="sm">
                            {{ $editMode ? __('training_plans.update_button') : __('training_plans.create_button') }}
                        </flux:button>
                    </div>
                </div>
            </div>

            <flux:separator variant="subtle" class="mt-8" />
        </form>
    </div>
</div>
