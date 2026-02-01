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
                <div class="flex items-center justify-between gap-4">
                    <flux:heading size="md" level="2">{{ __('training_plans.exercises_section') }}</flux:heading>
                    @if ($this->hasAiAccess)
                        <flux:button wire:click="openAiModal" size="sm" variant="ghost" icon="sparkles" type="button">
                            Generar con IA
                        </flux:button>
                    @else
                        <div class="relative group">
                            <flux:button size="sm" variant="ghost" icon="sparkles" type="button" disabled>
                                Generar con IA
                            </flux:button>
                            <div class="absolute bottom-full right-0 mb-2 hidden group-hover:block z-50">
                                <div class="bg-gray-900 text-white text-xs rounded py-2 px-3 whitespace-nowrap">
                                    Disponible solo en planes Pro y Equipo
                                    <div class="absolute top-full right-4 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Barra de progreso de uso de IA (solo si tiene acceso) --}}
                @if ($this->hasAiAccess && $this->aiUsage['has_limit'])
                    <div class="bg-zinc-50 dark:bg-zinc-900/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-800">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <flux:icon.sparkles class="w-4 h-4 text-violet-600 dark:text-violet-400" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Uso de IA este mes
                                </span>
                            </div>
                            <span class="text-sm font-semibold {{ $this->aiUsage['is_exceeded'] ? 'text-red-600 dark:text-red-400' : 'text-zinc-600 dark:text-zinc-400' }}">
                                {{ $this->aiUsage['used'] }} / {{ $this->aiUsage['limit'] }}
                            </span>
                        </div>
                        <div class="w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-2 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $this->aiUsage['percentage'] >= 90 ? 'bg-red-500' : ($this->aiUsage['percentage'] >= 70 ? 'bg-amber-500' : 'bg-violet-600') }}"
                                 style="width: {{ min(100, $this->aiUsage['percentage']) }}%">
                            </div>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-500 mt-2">
                            @if ($this->aiUsage['is_exceeded'])
                                Límite alcanzado. Se renovará el 1° del próximo mes.
                            @elseif ($this->aiUsage['percentage'] >= 90)
                                ¡Cuidado! Solo quedan {{ $this->aiUsage['available'] }} generaciones disponibles.
                            @else
                                {{ $this->aiUsage['available'] }} generaciones disponibles hasta fin de mes.
                            @endif
                        </p>
                    </div>
                @endif

                <div class="space-y-4">
                    {{-- Buscador --}}
                    <div class="relative">
                        <div class="flex gap-2 items-center">
                            <div class="flex-1">
                                <flux:input size="sm" wire:model.live.debounce.300ms="exerciseSearch"
                                    wire:blur="clearSearch" placeholder="{{ __('training_plans.search_exercise') }}" />
                            </div>
                            <flux:modal.trigger name="quick-create-exercise-plan">
                                <flux:button size="sm" variant="primary" icon="plus" type="button">
                                    Crear Nuevo
                                </flux:button>
                            </flux:modal.trigger>
                        </div>

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
                        @elseif(strlen($exerciseSearch) >= 2)
                            <div class="absolute z-20 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-md mt-1 shadow-sm w-full p-3">
                                <div class="text-center py-2">
                                    <p class="text-sm text-gray-600 dark:text-neutral-400 mb-2">
                                        No se encontraron ejercicios para <strong>"{{ $exerciseSearch }}"</strong>
                                    </p>
                                    <flux:modal.trigger name="quick-create-exercise-plan">
                                        <flux:button
                                            size="xs"
                                            variant="primary"
                                            icon="plus"
                                            type="button"
                                            @click="$dispatch('prefill-exercise-name', { name: '{{ $exerciseSearch }}' })">
                                            Crear ejercicio
                                        </flux:button>
                                    </flux:modal.trigger>
                                </div>
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
                                                <button type="button"
                                                    wire:click="viewExerciseDetails({{ $ex['id'] }}, {{ $i }})"
                                                    wire:loading.class="opacity-50 pointer-events-none"
                                                    wire:target="viewExerciseDetails({{ $ex['id'] }}, {{ $i }})"
                                                    class="hover:underline focus:outline-none text-left cursor-pointer inline-flex items-center gap-1.5">
                                                    <span class="inline-flex items-center justify-center w-4">
                                                        <svg wire:loading.remove wire:target="viewExerciseDetails({{ $ex['id'] }}, {{ $i }})" class="h-3.5 w-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
                                                        </svg>
                                                        <svg wire:loading wire:target="viewExerciseDetails({{ $ex['id'] }}, {{ $i }})" class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                    </span>
                                                    {{ $ex['name'] }}
                                                </button>
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

    {{-- Modal para crear ejercicio rápido --}}
    <flux:modal name="quick-create-exercise-plan" class="max-w-lg" variant="flyout">
        <form class="space-y-6">
            <div>
                <flux:heading size="lg">Crear Nuevo Ejercicio</flux:heading>
            </div>

            <livewire:tenant.exercises.quick-create />
        </form>
    </flux:modal>

    {{-- Modal para generar con IA --}}
    @if ($showAiModal)
        <flux:modal wire:model="showAiModal" class="max-w-md" variant="flyout" :closable="false">
            <form class="space-y-6" @submit.prevent>
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <flux:heading size="lg">Generar Plan con IA</flux:heading>
                        <flux:subheading>
                            Describe el tipo de plan que necesitas y la IA lo generará usando los ejercicios disponibles.
                        </flux:subheading>
                    </div>
                    <button type="button"
                            wire:click="closeAiModal"
                            wire:loading.attr="disabled"
                            wire:target="generateWithAi"
                            class="text-gray-400 hover:text-gray-600 dark:text-neutral-500 dark:hover:text-neutral-300 disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <flux:separator variant="subtle" />

                <div class="space-y-4">
                    <div wire:loading.class="opacity-50 pointer-events-none" wire:target="generateWithAi">
                        <flux:textarea
                            wire:model="aiPrompt"
                            label="¿Qué tipo de plan necesitas?"
                            placeholder="Ej: Plan de hipertrofia para principiantes, 4 días por semana, enfocado en tren superior e inferior"
                            rows="4"
                            required
                        />
                    </div>

                    <div class="text-xs text-gray-500 dark:text-neutral-400 space-y-1">
                        <p><strong>Reglas automáticas:</strong></p>
                        <p>• Si no especificas días, se usarán entre 3-5 días</p>
                        <p>• Mínimo 4 ejercicios por día (pueden ser más)</p>
                        <p>• Si la IA sugiere ejercicios que no existen, se crearán automáticamente</p>
                        <p>• Puedes editar el plan después de generarlo</p>
                    </div>
                </div>

                <flux:separator variant="subtle" />

                <div class="flex gap-2 justify-end">
                    <flux:button
                        wire:click="closeAiModal"
                        variant="ghost"
                        type="button"
                        wire:loading.attr="disabled"
                        wire:target="generateWithAi">
                        Cancelar
                    </flux:button>
                    <flux:button
                        wire:click="generateWithAi"
                        variant="primary"
                        icon="sparkles">
                        Generar Plan
                    </flux:button>
                </div>
            </form>
        </flux:modal>

        {{-- Overlay de bloqueo durante generación --}}
        <div wire:loading wire:target="generateWithAi">
            <div class="fixed inset-0 bg-black/80 flex items-center justify-center"
                 style="z-index: 999999; margin: 0 !important;">
                <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center border border-gray-200 dark:border-neutral-800">
                    <div class="relative">
                        <flux:icon.arrow-path class="animate-spin size-16 mx-auto mb-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <flux:heading size="lg" class="mb-2">Generando plan con IA</flux:heading>
                    <flux:subheading class="text-gray-600 dark:text-neutral-400">
                        Por favor espera, esto puede tomar unos segundos...
                    </flux:subheading>
                    <div class="mt-4 text-xs text-gray-500 dark:text-neutral-500">
                        No cierres esta ventana
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de detalles del ejercicio --}}
    <flux:modal wire:model.live="showExerciseDetails" class="max-w-2xl" variant="flyout">
        @if($selectedExerciseDetails)
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $selectedExerciseDetails['name'] }}</flux:heading>
                <flux:subheading>
                    <span class="inline-flex items-center gap-2">
                        <flux:badge size="sm" color="zinc">{{ $selectedExerciseDetails['category'] }}</flux:badge>
                        @if($selectedExerciseDetails['level'])
                            <flux:badge size="sm"
                                color="{{ $selectedExerciseDetails['level'] === 'beginner' ? 'green' : ($selectedExerciseDetails['level'] === 'intermediate' ? 'yellow' : 'red') }}">
                                {{ __('exercises.levels.' . $selectedExerciseDetails['level']) ?? ucfirst($selectedExerciseDetails['level']) }}
                            </flux:badge>
                        @endif
                    </span>
                </flux:subheading>
            </div>

            <flux:separator variant="subtle" />

            {{-- Imágenes --}}
            @if(!empty($selectedExerciseDetails['images']))
                <div class="grid grid-cols-2 gap-4">
                    @foreach($selectedExerciseDetails['images'] as $image)
                        <div class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-neutral-700">
                            <img src="{{ $image['url'] }}"
                                 alt="{{ $selectedExerciseDetails['name'] }}"
                                 class="w-full h-48 object-cover">
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Descripción --}}
            @if($selectedExerciseDetails['description'])
                <div>
                    <flux:heading size="sm" class="mb-2">{{ __('exercises.description') }}</flux:heading>
                    <p class="text-sm text-gray-700 dark:text-neutral-300 whitespace-pre-line">
                        {{ $selectedExerciseDetails['description'] }}
                    </p>
                </div>
            @endif

            {{-- Equipamiento --}}
            @if($selectedExerciseDetails['equipment'])
                <div>
                    <flux:heading size="sm" class="mb-2">{{ __('exercises.equipment') }}</flux:heading>
                    <p class="text-sm text-gray-700 dark:text-neutral-300">
                        {{ $selectedExerciseDetails['equipment'] }}
                    </p>
                </div>
            @endif

            {{-- Grupo muscular --}}
            @if($selectedExerciseDetails['muscle_group'])
                <div>
                    <flux:heading size="sm" class="mb-2">{{ __('exercises.muscle_group') }}</flux:heading>
                    <p class="text-sm text-gray-700 dark:text-neutral-300">
                        {{ $selectedExerciseDetails['muscle_group'] }}
                    </p>
                </div>
            @endif

            {{-- Video URL --}}
            @if($selectedExerciseDetails['video_url'])
                <div>
                    <flux:heading size="sm" class="mb-2">{{ __('exercises.video') }}</flux:heading>
                    <a href="{{ $selectedExerciseDetails['video_url'] }}"
                       target="_blank"
                       class="text-sm text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                        <x-icons.lucide.external-link class="size-4" />
                        Ver video
                    </a>
                </div>
            @endif

            <flux:separator variant="subtle" />

            {{-- Botón para editar --}}
            <div class="flex justify-end">
                <flux:button as="a"
                    href="{{ route('tenant.dashboard.exercises.edit', $selectedExerciseDetails['uuid']) }}"
                    target="_blank"
                    variant="ghost"
                    size="sm"
                    icon="pencil">
                    {{ __('common.edit') }}
                </flux:button>
            </div>
        </div>
        @endif
    </flux:modal>
</div>
