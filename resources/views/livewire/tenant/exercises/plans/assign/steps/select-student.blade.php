<div class="space-y-6">
    <flux:heading size="lg">{{ __('Paso 1 — Seleccionar alumno(s)') }}</flux:heading>
    <flux:subheading>{{ __('Elegí uno o varios alumnos activos a los que asignar la rutina.') }}</flux:subheading>

    <div class="flex items-center gap-3">
        <flux:input wire:model.live.debounce.500ms="q" placeholder="{{ __('Buscar por nombre o email...') }}" class="w-full" />
    </div>

    <div class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-neutral-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    {{ __('Nombre') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    {{ __('Email') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    {{ __('Acción') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            @forelse ($students as $student)
                                @php
                                    $selected = in_array($student->id, $state['student_ids'] ?? []);
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200">
                                        {{ $student->full_name ?? ($student->first_name.' '.$student->last_name) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $student->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                        <flux:button
                                            size="sm"
                                            variant="{{ $selected ? 'primary' : 'outline' }}"
                                            wire:click="toggleStudent({{ $student->id }})"
                                            class="inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent focus:outline-hidden">
                                            {{ $selected ? __('Seleccionado') : __('Seleccionar') }}
                                        </flux:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-neutral-400">
                                        {{ __('No se encontraron alumnos.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 flex justify-between items-center">
        <div>
            {{ $students->links('components.preline.pagination') }}
        </div>

        <flux:button variant="primary" wire:click="next">
            {{ __('Continuar') }}
        </flux:button>
    </div>
</div>
