<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6 space-y-6">

        {{-- 🔹 Header unificado --}}
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                {{-- 🔹 Bloque izquierda: avatar + nombre + subtítulo --}}
                <div class="flex items-center gap-4">
                    {{-- Avatar --}}
                    <div
                        class="h-12 w-12 rounded-full overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 flex items-center justify-center">
                        @if ($student->hasMedia('avatar'))
                            <img src="{{ $student->getFirstMediaUrl('avatar', 'thumb') }}" alt="{{ $student->full_name }}"
                                class="object-cover h-full w-full">
                        @else
                            <span class="text-sm font-semibold text-gray-600 dark:text-neutral-300">
                                {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                            </span>
                        @endif
                    </div>

                    {{-- Datos --}}
                    <div class="flex flex-col">
                        <div class="flex flex-wrap items-center gap-x-3">
                            <flux:heading size="xl" level="1" class="leading-none">
                                {{ $student->full_name }}
                            </flux:heading>
                            <span class="text-sm text-gray-500 dark:text-neutral-400 font-normal leading-none mt-[2px]">
                                — {{ __('students.training_plans_title') }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-neutral-400">
                            {{ $student->email }}
                        </div>
                    </div>
                </div>

                {{-- 🔹 Acciones --}}
                <div class="flex items-center gap-3">
                    <flux:button as="a" variant="ghost" size="sm"
                        href="{{ route('tenant.dashboard.students.edit', $student->uuid) }}" icon="arrow-left">
                        {{ __('site.back') }}
                    </flux:button>

                    <flux:button as="a"
                        href="{{ route('tenant.dashboard.training-plans.create', ['student' => $student->uuid]) }}"
                        variant="primary" icon="plus">
                        {{ __('students.assign_new_plan') }}
                    </flux:button>
                </div>
            </div>

            <flux:separator variant="subtle" class="mt-3" />
        </div>


        {{-- 🔹 Tabla principal --}}
        <section class="w-full">
            <x-data-table>
                <x-slot name="head">
                    <th
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-center w-16">
                        {{ __('training_plans.image') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('training_plans.name') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('training_plans.period') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('training_plans.status') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
                        {{ __('common.actions') }}
                    </th>
                </x-slot>

                {{-- Filas --}}
                @forelse ($plans as $plan)
                    @php
                        // Obtener imágenes desde exercises usando exercises_data
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
                    @endphp

                    <tr wire:key="plan-{{ $plan->uuid }}" class="divide-y divide-gray-200 dark:divide-neutral-700">
                        {{-- Imagen miniatura --}}
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

                        <td class="align-middle px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            <div class="flex items-center gap-2">
                                {{ $plan->name }}
                                @if ($plan->version_label)
                                    @php
                                        $isMajor = floor($plan->meta['version'] ?? 1) == ($plan->meta['version'] ?? 1);
                                        $style = $isMajor
                                            ? 'bg-purple-50 text-purple-700 ring-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:ring-purple-900'
                                            : 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-900';
                                    @endphp

                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $style }}">
                                        {{ $plan->version_label }}
                                    </span>
                                @endif
                            </div>
                        </td>


                        {{-- Periodo --}}
                        <td class="align-middle px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                            {{ $plan->assigned_from?->format('d/m/Y') ?? '—' }}
                            @if ($plan->assigned_until)
                                → {{ $plan->assigned_until->format('d/m/Y') }}
                            @endif
                        </td>

                        {{-- Estado --}}
                        <td class="align-middle px-6 py-4 text-sm">
                            @php
                                $state = $plan->is_current ? 'current' : ($plan->is_active ? 'active' : 'inactive');

                                if ($plan->assigned_until && $plan->assigned_until->isPast()) {
                                    $state = 'expired';
                                }

                                $styles = [
                                    'current' =>
                                        'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-950/40 dark:text-green-300 dark:ring-green-900',
                                    'active' =>
                                        'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-900',
                                    'inactive' =>
                                        'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800',
                                    'expired' =>
                                        'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800',
                                ];
                            @endphp

                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $styles[$state] }}">
                                {{ __('training_plans.' . $state) }}
                            </span>

                        </td>

                        {{-- Acciones --}}
                        <td class="align-middle px-6 py-4 text-end text-sm font-medium space-x-2">
                            <flux:button size="sm" wire:click="duplicatePlan('{{ $plan->uuid }}')"
                                variant="ghost">
                                {{ __('common.duplicate') }}
                            </flux:button>

                            <flux:button size="sm" as="a" wire:navigate
                                href="{{ route('tenant.dashboard.training-plans.edit', $plan->uuid) }}">
                                {{ __('common.edit') }}
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                            {{ __('students.no_training_plans') }}
                        </td>
                    </tr>
                @endforelse
            </x-data-table>
        </section>
    </div>
</div>
