<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <div class="space-y-6">
            {{-- Header sticky --}}
            <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
                <div class="flex items-center justify-between gap-4 max-w-3xl">
                    <div>
                        <flux:heading size="xl" level="1">
                            {{ __('students.plans_history_title') }}
                        </flux:heading>
                        <flux:subheading size="lg" class="mb-6">
                            {{ $student->full_name }}
                        </flux:subheading>
                    </div>
                    <flux:button as="a" wire:navigate
                        href="{{ $back === 'form' ? route('tenant.dashboard.students.edit', $student->uuid) : route('tenant.dashboard.students.index') }}"
                        variant="ghost" size="sm">
                        {{ __('common.back') }}
                    </flux:button>
                </div>
                <flux:separator variant="subtle" class="mt-2" />
            </div>

            {{-- Contenido --}}
            <div class="max-w-3xl space-y-6 pt-2">

    @if (session('success'))
        <div class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
    @endif

    <div class="space-y-4">
        @forelse ($assignments as $assignment)
            <div class="border rounded-lg
                        @if($assignment->status->value === 'active')
                            border-2 bg-white dark:bg-neutral-800/50
                        @else
                            border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-800
                        @endif"
                 @if($assignment->status->value === 'active') style="border-color: var(--ftt-color-base);" @endif>
                <div class="p-6 space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0 space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $assignment->name }}
                                </h3>

                                {{-- Badge de status --}}
                                @if ($assignment->status->value === 'active')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white whitespace-nowrap" style="background-color: var(--ftt-color-base);">
                                        <x-icons.lucide.play-circle class="w-3 h-3 mr-1.5 flex-shrink-0" />
                                        {{ $assignment->status->label() }}
                                    </span>
                                @elseif($assignment->status->value === 'pending')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white whitespace-nowrap" style="background-color: rgb(245 158 11);">
                                        <x-icons.lucide.clock class="w-3 h-3 mr-1.5 flex-shrink-0" />
                                        {{ $assignment->status->label() }}
                                    </span>
                                @elseif($assignment->status->value === 'completed')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white whitespace-nowrap" style="background-color: rgb(59 130 246);">
                                        <x-icons.lucide.check-circle class="w-3 h-3 mr-1.5 flex-shrink-0" />
                                        {{ $assignment->status->label() }}
                                    </span>
                                @elseif($assignment->status->value === 'cancelled')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white whitespace-nowrap" style="background-color: rgb(107 114 128);">
                                        <x-icons.lucide.x-circle class="w-3 h-3 mr-1.5 flex-shrink-0" />
                                        {{ $assignment->status->label() }}
                                    </span>
                                @endif

                                @if ($assignment->overrides['hidden'] ?? false)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-200 dark:bg-yellow-800 text-yellow-900 dark:text-yellow-100 whitespace-nowrap">
                                        {{ __('students.hidden') }}
                                    </span>
                                @endif
                            </div>
                            @if ($assignment->plan)
                                <p class="text-sm text-gray-600 dark:text-neutral-400">{{ $assignment->plan->name }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($assignment->status->value === 'pending')
                                <flux:modal.trigger name="confirm-activate-assignment">
                                    <flux:button
                                        size="sm"
                                        variant="primary"
                                        wire:click="$set('deleteAssignmentUuid', '{{ $assignment->uuid }}')"
                                        icon="play"
                                    >
                                        Activar ahora
                                    </flux:button>
                                </flux:modal.trigger>
                            @endif
                            
                            @if ($assignment->plan)
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    as="a"
                                    wire:navigate
                                    href="{{ route('tenant.dashboard.training-plans.edit', $assignment->plan) }}"
                                    icon="square-pen"
                                >
                                    Editar plantilla
                                </flux:button>
                                {{--
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    as="a"
                                    wire:navigate
                                    href="{{ route('tenant.dashboard.training-plans.edit', [$assignment->plan, 'assignment' => $assignment->uuid]) }}"
                                    icon="square-pen"
                                >
                                    Editar asignado
                                </flux:button>
                                --}}
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    as="a"
                                    href="{{ route('tenant.dashboard.students.plan-download', $assignment->uuid) }}"
                                    icon="download"
                                >
                                    {{ __('common.download') }} PDF
                                </flux:button>
                            @endif
                            @if (!$assignment->is_active)
                                <flux:modal.trigger name="confirm-delete-assignment">
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        wire:click="confirmDelete('{{ $assignment->uuid }}')"
                                        icon="trash-2"
                                    >
                                    </flux:button>
                                </flux:modal.trigger>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-wide {{ $assignment->is_active ? 'text-gray-200 dark:text-neutral-300' : 'text-gray-500 dark:text-neutral-500' }}">
                                {{ __('students.starts_at') }}
                            </p>
                            <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $assignment->starts_at->format('d/m/Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide {{ $assignment->is_active ? 'text-gray-200 dark:text-neutral-300' : 'text-gray-500 dark:text-neutral-500' }}">
                                {{ __('students.ends_at') }}
                            </p>
                            <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $assignment->ends_at->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>

                    @if ($assignment->plan && $assignment->exercises_snapshot)
                        <div class="pt-4 border-t dark:border-neutral-700">
                            <details class="cursor-pointer">
                                <summary class="text-sm font-medium text-gray-700 dark:text-neutral-300 flex items-center gap-2">
                                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    {{ __('students.view_exercises') }}
                                </summary>
                                <div class="mt-4 space-y-3">
                                    @if ($assignment->exercises_by_day)
                                        @foreach ($assignment->exercises_by_day as $day => $exercises)
                                            <div class="bg-gray-50 dark:bg-neutral-700/50 rounded p-3">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                                                    {{ __('students.day') }} {{ $day }}
                                                </p>
                                                <ul class="space-y-1 text-sm text-gray-600 dark:text-neutral-300">
                                                    @foreach ($exercises as $exercise)
                                                        <li class="flex items-start gap-2">
                                                            <span class="text-gray-400 mt-0.5">•</span>
                                                            <span>
                                                                {{ $exercise['name'] ?? 'N/A' }}
                                                                @if ($exercise['sets'] ?? null)
                                                                    <span class="text-gray-500 dark:text-neutral-500">({{ $exercise['sets'] }}x{{ $exercise['reps'] ?? '?' }})</span>
                                                                @endif
                                                                @php
                                                                    $prescription = $exercise['detail'] ?? $exercise['prescription'] ?? null;
                                                                    $notes = $exercise['notes'] ?? null;
                                                                @endphp
                                                                @if ($prescription)
                                                                    <div class="text-xs text-gray-500 dark:text-neutral-400">{{ $prescription }}</div>
                                                                @endif
                                                                @if ($notes)
                                                                    <div class="text-xs text-gray-500 dark:text-neutral-400">{{ $notes }}</div>
                                                                @endif
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </details>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="mt-4 text-gray-600 dark:text-neutral-400">
                    {{ __('students.no_plans_assigned') }}
                </p>
            </div>
        @endforelse
        <div>
            {{ $assignments->links('components.preline.pagination') }}
        </div>
            </div>
        </div>
    </div>
</div>

<flux:modal name="confirm-delete-assignment" class="min-w-[22rem]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('common.delete_title') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('common.delete_msg') }}
                @if ($deleteAssignmentName)
                    <strong class="block mt-1">{{ $deleteAssignmentName }}</strong>
                @endif
            </flux:text>
        </div>
        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="deleteAssignment" variant="danger">
                {{ __('common.confirm_delete') }}
            </flux:button>
        </div>
    </div>
</flux:modal>

<flux:modal name="confirm-activate-assignment" class="min-w-[22rem]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Activar plan ahora</flux:heading>
            <flux:text class="mt-2">
                ¿Estás seguro de que deseas activar este plan ahora?
                @if ($this->student->currentPlanAssignment)
                    <strong class="block mt-2 text-red-600 dark:text-red-400">
                        El plan activo actual será cancelado automáticamente.
                    </strong>
                @endif
            </flux:text>
        </div>
        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="activateNow('{{ $deleteAssignmentUuid }}')" variant="primary">
                Activar ahora
            </flux:button>
        </div>
    </div>
</flux:modal>
