<div class="space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Plantillas de ejercicio') }}</flux:heading>
        <flux:subheading class="mt-1">{{ __('Diseñá y reutilizá rutinas como plantillas.') }}</flux:subheading>
        <flux:separator variant="subtle" class="mt-4" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div>
            <flux:input wire:model.live.debounce.250ms="q" label="{{ __('Buscar') }}"
                placeholder="{{ __('Nombre, código o descripción') }}" />
        </div>

        <div>
            <flux:select wire:model.live="status" label="{{ __('Estado') }}">
                <option value="">{{ __('Todos') }}</option>
                <option value="draft">{{ __('Borrador') }}</option>
                <option value="published">{{ __('Publicado') }}</option>
                <option value="archived">{{ __('Archivado') }}</option>
                <option value="trashed">{{ __('Papelera') }}</option>
            </flux:select>
        </div>

        <div>
            <div>
                <flux:select wire:model.live="perPage" label="{{ __('Por página') }}">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                </flux:select>
            </div>
        </div>
        <div class="flex gap-2 justify-end">


            <flux:button as="a" href="{{ route('tenant.dashboard.exercises.plans.templates.create') }}">
                {{ __('Nueva plantilla') }}</flux:button>



        </div>
    </div>


    <div class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead>
                            <tr>
                                {{-- Nombre --}}
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    <button type="button" class="inline-flex items-center gap-1 hover:underline"
                                        wire:click="sortBy('name')">
                                        {{ __('Nombre') }}
                                        @if ($sortField === 'name')
                                            <span class="text-[10px]">{{ $sortDir === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </button>
                                </th>

                                {{-- Código --}}
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    <button type="button" class="inline-flex items-center gap-1 hover:underline"
                                        wire:click="sortBy('code')">
                                        {{ __('Código') }}
                                        @if ($sortField === 'code')
                                            <span class="text-[10px]">{{ $sortDir === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </button>
                                </th>

                                {{-- Estado --}}
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    <button type="button" class="inline-flex items-center gap-1 hover:underline"
                                        wire:click="sortBy('status')">
                                        {{ __('Estado') }}
                                        @if ($sortField === 'status')
                                            <span class="text-[10px]">{{ $sortDir === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </button>
                                </th>

                                {{-- Versión --}}
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    <button type="button" class="inline-flex items-center gap-1 hover:underline"
                                        wire:click="sortBy('version')">
                                        {{ __('Versión') }}
                                        @if ($sortField === 'version')
                                            <span class="text-[10px]">{{ $sortDir === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </button>
                                </th>

                                {{-- Acciones (sin sort) --}}
                                <th scope="col"
                                    class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            @forelse ($templates as $tpl)
                                @php
                                    $status = $tpl->status;
                                    $badgeMap = [
                                        'published' =>
                                            'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-400/10 dark:text-emerald-300 dark:border-emerald-500/30',
                                        'draft' =>
                                            'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-400/10 dark:text-amber-300 dark:border-amber-500/30',
                                        'archived' =>
                                            'bg-gray-100 text-gray-700 border-gray-200 dark:bg-neutral-700/60 dark:text-neutral-300 dark:border-neutral-600',
                                    ];
                                @endphp

                                <tr wire:key="tpl-row-{{ $tpl->id }}">
                                    {{-- Nombre --}}
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200">
                                        {{ $tpl->name ?: __('(Sin nombre)') }}
                                    </td>

                                    {{-- Código --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $tpl->code }}
                                    </td>

                                    {{-- Estado (badge) --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                                        <span
                                            class="inline-flex items-center gap-x-1.5 rounded-md border px-2 py-0.5 text-[11px] leading-4 font-medium {{ $badgeMap[$status] ?? 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-neutral-700/60 dark:text-neutral-300 dark:border-neutral-600' }}">
                                            {{ __($status) }}
                                        </span>
                                        @if (method_exists($tpl, 'trashed') && $tpl->trashed())
                                            <span
                                                class="ml-2 inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] leading-4 font-medium bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-400/10 dark:text-rose-300 dark:border-rose-500/30">
                                                {{ __('Papelera') }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Versión --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $tpl->version }}
                                    </td>

                                    {{-- Acciones (tus botones, sin spans de loading) --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                        @php
                                            // helper visual: grupo primario (acciones frecuentes) + secundario (mantenimiento)
                                            $divider =
                                                '<span class="inline-block h-5 w-px mx-2 align-middle bg-gray-200 dark:bg-neutral-700"></span>';
                                        @endphp

                                        @if (!method_exists($tpl, 'trashed') || !$tpl->trashed())
                                            @if ($tpl->status === 'draft')
                                                <div class="inline-flex items-center justify-end gap-1.5">
                                                    {{-- Grupo primario --}}
                                                    <div class="inline-flex gap-1.5 shrink-0">
                                                        <flux:button as="a"
                                                            href="{{ route('tenant.dashboard.exercises.plans.templates.edit', ['template' => $tpl->id]) }}"
                                                            size="sm">
                                                            {{ __('Editar') }}
                                                        </flux:button>
                                                        <flux:button wire:click="duplicate({{ $tpl->id }})"
                                                            wire:target="duplicate({{ $tpl->id }})"
                                                            wire:loading.attr="disabled" size="sm">
                                                            {{ __('Duplicar') }}
                                                        </flux:button>
                                                        <flux:button wire:click="publish({{ $tpl->id }})"
                                                            wire:target="publish({{ $tpl->id }})"
                                                            wire:loading.attr="disabled" size="sm">
                                                            {{ __('Publicar') }}
                                                        </flux:button>
                                                    </div>

                                                    {!! $divider !!}

                                                    {{-- Grupo secundario --}}
                                                    <div class="inline-flex gap-1.5 shrink-0">
                                                        <flux:button wire:click="archive({{ $tpl->id }})"
                                                            wire:target="archive({{ $tpl->id }})"
                                                            wire:loading.attr="disabled" variant="ghost" size="sm">
                                                            {{ __('Archivar') }}
                                                        </flux:button>
                                                    </div>
                                                </div>
                                            @elseif ($tpl->status === 'published')
                                                <div class="inline-flex items-center justify-end gap-1.5">
                                                    <div class="inline-flex gap-1.5 shrink-0">
                                                        <flux:button wire:click="duplicate({{ $tpl->id }})"
                                                            wire:target="duplicate({{ $tpl->id }})"
                                                            wire:loading.attr="disabled" size="sm">
                                                            {{ __('Duplicar') }}
                                                        </flux:button>
                                                    </div>

                                                    {!! $divider !!}

                                                    <div class="inline-flex gap-1.5 shrink-0">
                                                        <flux:button wire:click="archive({{ $tpl->id }})"
                                                            wire:target="archive({{ $tpl->id }})"
                                                            wire:loading.attr="disabled" variant="ghost" size="sm">
                                                            {{ __('Archivar') }}
                                                        </flux:button>
                                                    </div>
                                                </div>
                                            @else
                                                {{-- archived --}}
                                                <div class="inline-flex items-center justify-end gap-1.5">
                                                    <div class="inline-flex gap-1.5 shrink-0">
                                                        <flux:button wire:click="unarchive({{ $tpl->id }})"
                                                            wire:target="unarchive({{ $tpl->id }})"
                                                            wire:loading.attr="disabled" variant="ghost" size="sm">
                                                            {{ __('Desarchivar') }}
                                                        </flux:button>
                                                        <flux:button wire:click="duplicate({{ $tpl->id }})"
                                                            wire:target="duplicate({{ $tpl->id }})"
                                                            wire:loading.attr="disabled" size="sm">
                                                            {{ __('Duplicar') }}
                                                        </flux:button>
                                                    </div>

                                                    {!! $divider !!}

                                                    <div class="inline-flex gap-1.5 shrink-0">
                                                        <flux:button wire:click="delete({{ $tpl->id }})"
                                                            wire:target="delete({{ $tpl->id }})"
                                                            wire:loading.attr="disabled" variant="ghost"
                                                            size="sm">
                                                            {{ __('Eliminar') }}
                                                        </flux:button>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            {{-- Trashed --}}
                                            <div class="inline-flex items-center justify-end gap-1.5">
                                                <div class="inline-flex gap-1.5 shrink-0">
                                                    <flux:button wire:click="restore({{ $tpl->id }})"
                                                        wire:target="restore({{ $tpl->id }})"
                                                        wire:loading.attr="disabled" size="sm">
                                                        {{ __('Restaurar') }}
                                                    </flux:button>
                                                </div>

                                                {!! $divider !!}

                                                <div class="inline-flex gap-1.5 shrink-0">
                                                    <flux:button wire:click="forceDelete({{ $tpl->id }})"
                                                        wire:target="forceDelete({{ $tpl->id }})"
                                                        wire:loading.attr="disabled" variant="ghost" size="sm">
                                                        {{ __('Eliminar definitivamente') }}
                                                    </flux:button>
                                                </div>
                                            </div>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="px-6 py-8 text-center text-gray-500 dark:text-neutral-400">
                                        {{ __('Sin resultados') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="p-3">
                        {{ $templates->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
