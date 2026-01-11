<section class="w-full">
    <x-data-table :pagination="$this->manuals">
        {{-- Filtros --}}
        <x-slot name="filters">
            <x-index-filters :searchPlaceholder="__('manuals.search_placeholder')">
                <x-slot name="additionalFilters">
                    {{-- Filtro por Categoría --}}
                    <div class="min-w-[160px]">
                        <flux:select size="sm" wire:model.live="categoryFilter" :label="__('manuals.category')">
                            <option value="">{{ __('common.all') }}</option>
                            @foreach ($this->categories as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                </x-slot>
            </x-index-filters>
        </x-slot>

        {{-- Cabecera --}}
        <x-slot name="head">
            <th wire:click="sort('title')"
                class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                <span class="inline-flex items-center gap-1">
                    {{ __('manuals.title') }}
                    @if ($sortBy === 'title')
                        {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                    @endif
                </span>
            </th>
            <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                {{ __('manuals.category') }}
            </th>
            <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                {{ __('common.status') }}
            </th>
            <th wire:click="sort('updated_at')"
                class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                <span class="inline-flex items-center gap-1">
                    {{ __('manuals.updated_at') }}
                    @if ($sortBy === 'updated_at')
                        {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                    @endif
                </span>
            </th>
            <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
                {{ __('common.actions') }}
            </th>
        </x-slot>

        {{-- Filas --}}
        @forelse ($this->manuals as $manual)
            <tr wire:key="manual-{{ $manual->uuid }}">
                {{-- Título + Resumen con ícono/imagen --}}
                <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                    <div class="inline-flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 flex items-center justify-center">
                            @if ($manual->hasMedia('icon'))
                                <img src="{{ $manual->getFirstMediaUrl('icon') }}" alt="{{ $manual->title }}" class="object-cover h-full w-full">
                            @else
                                <span class="text-xs font-semibold">{{ strtoupper(mb_substr($manual->title, 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="leading-tight">
                            <div class="font-medium text-gray-900 dark:text-neutral-100 flex items-center gap-2">
                                {{ $manual->title }}
                                @if ($manual->hasMedia('attachments'))
                                    <flux:icon.paper-clip class="w-4 h-4 text-zinc-400" title="{{ $manual->getMedia('attachments')->count() }} archivo(s)" />
                                @endif
                            </div>
                            @if($manual->summary)
                                <div class="text-xs text-gray-500 dark:text-neutral-400">{{ \Illuminate\Support\Str::limit($manual->summary, 60) }}</div>
                            @endif
                        </div>
                    </div>
                </td>

                {{-- Categoría --}}
                <td class="align-top px-6 py-4 text-sm">
                    @php
                        $categoryStyles = [
                            'configuration' => 'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:ring-purple-900',
                            'training'      => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-900',
                            'nutrition'     => 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-950/40 dark:text-green-300 dark:ring-green-900',
                            'support'       => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-900',
                            'general'       => 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800',
                        ];
                        $categoryStyle = $categoryStyles[$manual->category->value] ?? $categoryStyles['general'];
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $categoryStyle }}">
                        {{ $manual->category->label() }}
                    </span>
                </td>

                {{-- Estado (activo/inactivo) --}}
                <td class="align-top px-6 py-4 text-sm">
                    @if($manual->is_active && $manual->published_at && $manual->published_at <= now())
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-950/40 dark:text-green-300 dark:ring-green-900">
                            {{ __('manuals.published') }}
                        </span>
                    @elseif($manual->is_active)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-900">
                            {{ __('manuals.active') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800">
                            {{ __('manuals.inactive') }}
                        </span>
                    @endif
                </td>

                {{-- Fecha de actualización --}}
                <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                    {{ $manual->updated_at->format('d/m/Y') }}
                </td>

                {{-- Acciones --}}
                <td class="align-top px-6 py-4 text-end text-sm font-medium">
                    <span class="inline-flex items-center gap-2 text-xs text-gray-400 dark:text-neutral-500 whitespace-nowrap">
                        <flux:button size="sm" as="a" wire:navigate href="{{ route('central.dashboard.manuals.edit', $manual->uuid) }}">
                            {{ __('common.edit') }}
                        </flux:button>

                        <flux:modal.trigger name="confirm-delete-manual">
                            <flux:button size="sm" variant="ghost" wire:click="confirmDelete('{{ $manual->uuid }}')">
                                {{ __('common.delete') }}
                            </flux:button>
                        </flux:modal.trigger>
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                    {{ __('manuals.no_manuals') }}
                </td>
            </tr>
        @endforelse

        {{-- Modal --}}
        <x-slot name="modal">
            <flux:modal name="confirm-delete-manual" class="min-w-[22rem]" x-data
                @manual-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-manual' })">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('manuals.delete_confirm_title') }}</flux:heading>
                        <flux:text class="mt-2">{{ __('manuals.delete_confirm_msg') }}</flux:text>
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
    </x-data-table>
</section>
