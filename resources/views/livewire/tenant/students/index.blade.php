<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6">
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('site.students') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">
                        {{ __('site.students_subheading') }}
                    </flux:subheading>
                </div>
                <flux:button as="a" href="{{ route('tenant.dashboard.students.create') }}" variant="primary"
                    icon="plus">
                    {{ __('site.new_student') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>

        <section class="w-full">
            <x-data-table :pagination="$students">
                {{-- FILTROS --}}
                <x-slot name="filters">
                    <div class="flex flex-wrap gap-4 w-full items-end">
                        <div class="max-w-[260px] flex-1">
                            <flux:label class="text-xs">{{ __('site.search') }}</flux:label>
                            <flux:input size="sm" wire:model.live.debounce.400ms="search"
                                placeholder="{{ __('site.search_placeholder_students') }}" class="w-full" />
                        </div>

                        <div class="min-w-[150px]">
                            <flux:label class="text-xs">{{ __('site.status') }}</flux:label>
                            <flux:select size="sm" wire:model="status">
                                <option value="">{{ __('site.all') }}</option>
                                <option value="active">{{ __('site.active') }}</option>
                                <option value="paused">{{ __('site.paused') }}</option>
                                <option value="inactive">{{ __('site.inactive') }}</option>
                                <option value="prospect">{{ __('site.prospect') }}</option>
                            </flux:select>
                        </div>

                        <div class="min-w-[220px]">
                            <flux:label class="text-xs">{{ __('site.commercial_plan') }}</flux:label>
                            <flux:select size="sm" wire:model="planId">
                                <option value="">{{ __('site.all') }}</option>
                                @foreach ($plans as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="min-w-[220px]">
                            <flux:label class="text-xs">{{ __('site.primary_goal') }}</flux:label>
                            <flux:select size="sm" wire:model="goalId">
                                <option value="">{{ __('site.all') }}</option>
                                @foreach ($goals as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="min-w-[200px]">
                            <flux:label class="text-xs">{{ __('site.tag') }}</flux:label>
                            <flux:select size="sm" wire:model="tagId">
                                <option value="">{{ __('site.all') }}</option>
                                @foreach ($tags as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <flux:button size="sm" variant="ghost" wire:click="filter" class="self-end">
                            {{ __('site.filter') }}
                        </flux:button>
                    </div>
                </x-slot>

                {{-- CABECERA --}}
                <x-slot name="head">
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer text-left"
                        wire:click="sort('last_name')">
                        <span class="inline-flex items-center gap-1">{{ __('site.student') }}
                            @if ($sortBy === 'last_name')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>

                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer text-left"
                        wire:click="sort('status')">
                        <span class="inline-flex items-center gap-1">{{ __('site.status') }}
                            @if ($sortBy === 'status')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>

                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 text-left">
                        {{ __('site.commercial_plan') }}
                    </th>

                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 text-left">
                        {{ __('site.primary_goal') }}
                    </th>

                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer text-left"
                        wire:click="sort('last_login_at')">
                        <span class="inline-flex items-center gap-1">{{ __('site.last_access') }}
                            @if ($sortBy === 'last_login_at')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>

                    <th class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer"
                        wire:click="sort('avg_adherence_pct')">
                        <span class="inline-flex items-center gap-1">{{ __('site.adherence') }}
                            @if ($sortBy === 'avg_adherence_pct')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>

                    <th class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                        {{ __('site.actions') }}
                    </th>
                </x-slot>

                {{-- FILAS --}}
                @forelse ($students as $s)
                    <tr>
                        {{-- Alumno --}}
                        <td class="align-top px-6 py-4 text-sm font-medium text-gray-800 dark:text-neutral-200">
                            <div class="inline-flex items-center gap-3">
                                <div
                                    class="h-8 w-8 rounded-full overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 flex items-center justify-center">
                                    @php
                                        // Si está cargada la relación, usamos eso; si no, Spatie consulta.
                                        $hasAvatar = $s->relationLoaded('media')
                                            ? $s->getMedia('avatar')->isNotEmpty()
                                            : $s->hasMedia('avatar');

                                        // Intentar usar conversión 'thumb' si existe; si no, original
                                        $avatarUrl = $hasAvatar
                                            ? ($s->getFirstMediaUrl('avatar', 'thumb') ?:
                                            $s->getFirstMediaUrl('avatar'))
                                            : null;
                                    @endphp

                                    @if ($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="avatar"
                                            class="h-full w-full object-cover" loading="lazy">
                                    @else
                                        <span class="text-[10px] font-semibold text-gray-700 dark:text-neutral-200">
                                            {{ strtoupper(substr($s->first_name ?? '', 0, 1) . substr($s->last_name ?? '', 0, 1)) ?: 'AA' }}
                                        </span>
                                    @endif
                                </div>

                                <div class="leading-tight">
                                    <div>{{ $s->full_name ?: '—' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                                        {{ $s->email ?? '—' }} • {{ $s->phone ?? '—' }}
                                    </div>
                                </div>
                            </div>

                        </td>

                        {{-- Estado --}}
                        <td class="align-top px-6 py-4 text-sm">
                            @php
                                $map = [
                                    'active' => 'text-green-600 dark:text-green-400',
                                    'paused' => 'text-amber-600 dark:text-amber-400',
                                    'inactive' => 'text-gray-500',
                                    'prospect' => 'text-blue-600 dark:text-blue-400',
                                ];
                            @endphp
                            <span class="text-xs {{ $map[$s->status] ?? 'text-gray-500' }}">
                                {{ ucfirst(__($s->status)) }}
                            </span>
                        </td>

                        {{-- Plan --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $s->commercialPlan->name ?? '—' }}
                        </td>

                        {{-- Objetivo --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $s->primaryTrainingGoal->name ?? '—' }}
                        </td>

                        {{-- Último acceso --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ optional($s->last_login_at)->diffForHumans() ?? '—' }}
                        </td>

                        {{-- Adherencia --}}
                        <td class="align-top px-6 py-4 text-end text-sm text-gray-900 dark:text-neutral-100">
                            @if (!is_null($s->avg_adherence_pct))
                                {{ rtrim(rtrim(number_format($s->avg_adherence_pct, 1, ',', '.'), '0'), ',') }}%
                            @else
                                —
                            @endif
                        </td>

                        {{-- Acciones --}}
                        <td class="align-top px-6 py-4 text-end text-sm font-medium">
                            <span
                                class="text-xs text-gray-400 dark:text-neutral-500 inline-flex items-center whitespace-nowrap gap-2">
                                <flux:button wire:navigate size="sm"
                                    href="{{ route('tenant.dashboard.students.edit', $s) }}">
                                    {{ __('site.edit') }}
                                </flux:button>
                                <flux:modal.trigger name="confirm-delete-student">
                                    <flux:button size="sm" variant="ghost" type="button"
                                        wire:click="confirmDelete({{ $s->id }})">
                                        {{ __('site.delete') }}
                                    </flux:button>
                                </flux:modal.trigger>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                            {{ __('site.no_students_found') }}
                        </td>
                    </tr>
                @endforelse

                {{-- MODAL --}}
                <x-slot name="modal">
                    <flux:modal name="confirm-delete-student" class="min-w-[22rem]" x-data
                        @student-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-student' })">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __('site.delete_student_title') }}</flux:heading>
                                <flux:text class="mt-2">
                                    {{ __('site.delete_student_message') }}
                                </flux:text>
                            </div>
                            <div class="flex gap-2">
                                <flux:spacer />
                                <flux:modal.close>
                                    <flux:button variant="ghost">{{ __('site.cancel') }}</flux:button>
                                </flux:modal.close>
                                <flux:button wire:click="delete" variant="danger">
                                    {{ __('site.confirm_delete') }}
                                </flux:button>
                            </div>
                        </div>
                    </flux:modal>
                </x-slot>
            </x-data-table>
        </section>
    </div>
</div>
