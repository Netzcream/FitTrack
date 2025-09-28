<div class="space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Plantillas de ejercicio') }}</flux:heading>
        <flux:subheading class="mt-1">{{ __('Diseñá y reutilizá rutinas como plantillas.') }}</flux:subheading>
        <flux:separator variant="subtle" class="mt-4" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div class="md:col-span-2">
            <flux:input wire:model.debounce.400ms="q" label="{{ __('Buscar') }}" placeholder="{{ __('Nombre, código o descripción') }}" />
        </div>
        <div>
            <flux:select wire:model="status" label="{{ __('Estado') }}">
                <option value="">{{ __('Todos') }}</option>
                <option value="draft">{{ __('Borrador') }}</option>
                <option value="published">{{ __('Publicado') }}</option>
                <option value="archived">{{ __('Archivado') }}</option>
            </flux:select>
        </div>
        <div class="flex gap-2">
            <flux:select wire:model="perPage" label="{{ __('Por página') }}">
                <option>10</option><option>25</option><option>50</option>
            </flux:select>
            <a href="{{ route('tenant.dashboard.exercises.plans.templates.create') }}" class="ml-auto">
                <flux:button>{{ __('Nueva plantilla') }}</flux:button>
            </a>
        </div>
    </div>

    <!-- Card/tabla estilo Preline -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-neutral-800/60">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                            {{ __('Nombre') }}
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                            {{ __('Código') }}
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                            {{ __('Estado') }}
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                            {{ __('Versión') }}
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                            {{ __('Acciones') }}
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
                    @forelse ($templates as $tpl)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-neutral-800/40">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-800 dark:text-neutral-200 font-medium">
                                {{ $tpl->name }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-neutral-300">
                                {{ $tpl->code }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @php
                                    $status = $tpl->status;
                                    $map = [
                                        'published' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-400/10 dark:text-emerald-300 dark:border-emerald-500/30',
                                        'draft'     => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-400/10 dark:text-amber-300 dark:border-amber-500/30',
                                        'archived'  => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-neutral-700/50 dark:text-neutral-300 dark:border-neutral-600',
                                    ];
                                @endphp
                                <span class="inline-flex items-center gap-x-1.5 rounded-md border px-2 py-1 text-xs font-medium {{ $map[$status] ?? 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-neutral-700/50 dark:text-neutral-300 dark:border-neutral-600' }}">
                                    {{ __($status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-neutral-300">
                                {{ $tpl->version }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1.5 justify-end">
                                    <a href="{{ route('tenant.dashboard.exercises.plans.templates.edit', $tpl->id) }}">
                                        <flux:button variant="ghost" size="sm">{{ __('Editar') }}</flux:button>
                                    </a>
                                    <flux:button wire:click="duplicate({{ $tpl->id }})" variant="ghost" size="sm">{{ __('Duplicar') }}</flux:button>
                                    @if($tpl->status !== 'published')
                                        <flux:button wire:click="publish({{ $tpl->id }})" variant="ghost" size="sm">{{ __('Publicar') }}</flux:button>
                                    @endif
                                    <flux:button wire:click="delete({{ $tpl->id }})" variant="danger" size="sm">{{ __('Eliminar') }}</flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-neutral-400">
                                {{ __('Sin resultados') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación: envoltorio estilo Preline -->
    <div class="mt-4">
        {{ $templates->onEachSide(1)->links() }}
    </div>
</div>
