<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6 space-y-6">

        {{-- Header --}}
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('Chats con alumnos') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">{{ __('Conversaciones con tus alumnos') }}</flux:subheading>
                </div>

                <flux:modal.trigger name="new-conversation">
                    <flux:button variant="primary" icon="plus">
                        {{ __('Nueva conversación') }}
                    </flux:button>
                </flux:modal.trigger>
            </div>
            <flux:separator variant="subtle" />
        </div>

        <section class="w-full">
            <x-data-table :pagination="$conversations">

                {{-- Filtros --}}
                <x-slot name="filters">
                    <x-index-filters :searchPlaceholder="__('Buscar por alumno...')" />
                </x-slot>

                {{-- Cabecera --}}
                <x-slot name="head">
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('Alumno') }}
                    </th>

                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('Último mensaje') }}
                    </th>

                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-center">
                        {{ __('No leídos') }}
                    </th>

                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-center">
                        {{ __('Fecha') }}
                    </th>

                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
                        {{ __('common.actions') }}
                    </th>
                </x-slot>

                {{-- Filas --}}
                @forelse ($conversations as $conversation)
                    <tr wire:key="conversation-{{ $conversation->id }}"
                        onclick="window.Livewire.navigate('{{ route('tenant.dashboard.messages.conversations.show', $conversation) }}')"
                        class="hover:bg-gray-50 dark:hover:bg-neutral-800/50 cursor-pointer transition-colors">

                        {{-- Estudiante --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            <div class="inline-flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 flex items-center justify-center">
                                    @if ($conversation->student?->hasMedia('avatar'))
                                        <img src="{{ $conversation->student->getFirstMediaUrl('avatar', 'thumb') }}"
                                             alt="{{ $conversation->student->full_name }}"
                                             class="object-cover h-full w-full">
                                    @else
                                        <span class="text-xs font-semibold">
                                            {{ strtoupper(substr($conversation->student?->first_name ?? 'S',0,1).substr($conversation->student?->last_name ?? 'N',0,1)) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="leading-tight">
                                    <div class="font-medium text-gray-900 dark:text-neutral-100">
                                        {{ $conversation->student?->full_name ?? __('Sin alumno') }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                                        {{ $conversation->student?->email }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Último mensaje --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                            @if ($conversation->lastMessage)
                                <div class="flex items-start gap-2 max-w-md">
                                    <div class="flex-1 min-w-0">
                                        <div class="inline-block bg-gray-100 dark:bg-neutral-700 rounded-lg px-3 py-2 max-w-full">
                                            <p class="text-[10px] font-semibold text-gray-500 dark:text-neutral-400 mb-0.5">
                                                {{ $conversation->lastMessage->sender_type === 'App\\Models\\Tenant\\Student' ? $conversation->student?->full_name : 'Tú' }}
                                            </p>
                                            <p class="text-sm text-gray-800 dark:text-neutral-200 break-words line-clamp-2">
                                                {{ $conversation->lastMessage->body }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-gray-400 dark:text-neutral-600">{{ __('Sin mensajes') }}</span>
                            @endif
                        </td>

                        {{-- No leídos --}}
                        <td class="px-6 py-4 text-sm text-center align-middle">
                            @if ($conversation->unread_count > 0)
                                <span class="inline-flex items-center justify-center h-6 w-6 rounded-full text-white text-xs font-bold"
                                      style="background-color: var(--ftt-color-base);">
                                    {{ $conversation->unread_count }}
                                </span>
                            @else
                                <span class="text-gray-400 dark:text-neutral-600">-</span>
                            @endif
                        </td>

                        {{-- Fecha --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400 text-center">
                            {{ $conversation->last_message_at?->diffForHumans() ?? '-' }}
                        </td>

                        {{-- Acciones --}}
                        <td class="align-top px-6 py-4 text-end text-sm font-medium" onclick="event.stopPropagation()">
                            <flux:button size="sm" as="a" wire:navigate
                                         href="{{ route('tenant.dashboard.messages.conversations.show', $conversation) }}">
                                {{ __('Ver') }}
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-sm text-center text-gray-500 dark:text-neutral-400">
                            <div class="flex flex-col items-center gap-2">
                                <x-icons.lucide.inbox class="w-12 h-12 text-gray-300 dark:text-neutral-600" />
                                <p class="font-medium">{{ __('No hay conversaciones') }}</p>
                                <p class="text-xs">{{ __('Las conversaciones aparecerán cuando los alumnos envíen mensajes') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse

                <x-slot name="modal">
                    {{-- Modal para nueva conversación --}}
                    <flux:modal name="new-conversation" class="min-w-[28rem]">
                        <form wire:submit.prevent="startConversation">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">{{ __('Nueva conversación') }}</flux:heading>
                                    <flux:subheading>{{ __('Selecciona un alumno para iniciar una conversación') }}</flux:subheading>
                                </div>

                                <div>
                                    <flux:select wire:model.defer="selectedStudentId" :label="__('Alumno')">
                                        <option value="">{{ __('Selecciona un alumno') }}</option>
                                        @foreach ($students as $student)
                                            <option value="{{ $student->id }}">
                                                {{ $student->first_name }} {{ $student->last_name }} ({{ $student->email }})
                                            </option>
                                        @endforeach
                                    </flux:select>
                                </div>

                                <div class="flex gap-2">
                                    <flux:spacer />
                                    <flux:modal.close>
                                        <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                                    </flux:modal.close>
                                    <flux:button type="submit" variant="primary">
                                        {{ __('Iniciar conversación') }}
                                    </flux:button>
                                </div>
                            </div>
                        </form>
                    </flux:modal>
                </x-slot>

            </x-data-table>
        </section>

    </div>
</div>
