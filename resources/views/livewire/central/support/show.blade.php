<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" level="1">
                    Soporte: {{ $conversation->tenant->name ?? 'Tenant #' . $conversation->tenant->id }}
                </flux:heading>
                <flux:subheading size="lg">Conversación de soporte técnico</flux:subheading>
            </div>
            <flux:button
                as="a"
                href="{{ route('central.dashboard.support.index') }}"
                variant="ghost"
                icon="arrow-left"
                wire:navigate
            >
                Volver
            </flux:button>
        </div>
    </div>

    {{-- Chat Container --}}
    <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg overflow-hidden shadow-sm">

        {{-- Messages list --}}
        <div class="h-[500px] overflow-y-auto p-4 space-y-4"
             x-data="{
                 shouldScroll: true,
                 messageCount: {{ $messages->count() }},
                 originalTitle: document.title,
                 titleInterval: null,
                 titleToggle: false,
                 justSentMessage: false,
                 scrollIfNeeded() {
                     const container = document.getElementById('messages-container-central');
                     if (container) {
                         const newCount = container.children.length;
                         if (newCount > this.messageCount && this.shouldScroll) {
                             this.messageCount = newCount;
                             this.$nextTick(() => { this.$el.scrollTop = this.$el.scrollHeight });
                             // Only notify if it's a received message, not sent by user
                             if (!this.justSentMessage) {
                                 this.startTitleNotification();
                             }
                             this.justSentMessage = false;
                         }
                     }
                 },
                 startTitleNotification() {
                     if (this.titleInterval) return; // Already running
                     this.titleInterval = setInterval(() => {
                         this.titleToggle = !this.titleToggle;
                         document.title = this.titleToggle ? '¡Tienes un nuevo mensaje!' : this.originalTitle;
                     }, 1000);
                 },
                 stopTitleNotification() {
                     if (this.titleInterval) {
                         clearInterval(this.titleInterval);
                         this.titleInterval = null;
                         document.title = this.originalTitle;
                         this.titleToggle = false;
                     }
                 }
             }"
             x-init="$el.scrollTop = $el.scrollHeight; setInterval(() => scrollIfNeeded(), 3100); window.addEventListener('focus', () => { stopTitleNotification(); $wire.markAsRead(); }); window.addEventListener('beforeunload', () => stopTitleNotification());"
             @scroll="shouldScroll = Math.abs($el.scrollHeight - $el.scrollTop - $el.clientHeight) < 50"
             @message-sent.window="justSentMessage = true; $wire.markAsRead(); setTimeout(() => { $el.scrollTop = $el.scrollHeight; stopTitleNotification(); }, 100)">

            <div wire:poll.3s class="space-y-4" id="messages-container-central">
                @forelse ($messages as $message)
                    {{-- Unread messages divider --}}
                    @if($firstUnreadMessageId && $message->id === $firstUnreadMessageId)
                        <div class="flex items-center gap-3 py-3" id="unread-divider">
                            <div class="flex-1 h-px bg-gradient-to-r from-transparent via-red-400 dark:via-red-500 to-transparent"></div>
                            <span class="text-xs font-medium text-red-500 dark:text-red-400 px-3 py-1 bg-red-50 dark:bg-red-950/30 rounded-full border border-red-200 dark:border-red-800">
                                {{ __('Mensajes no leídos') }}
                            </span>
                            <div class="flex-1 h-px bg-gradient-to-r from-transparent via-red-400 dark:via-red-500 to-transparent"></div>
                        </div>
                    @endif

                    <div class="flex {{ $message->sender_type->value === 'central' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[70%] {{ $message->sender_type->value === 'central' ? 'bg-blue-500 text-white' : 'bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100' }} rounded-lg px-4 py-2 shadow-sm">
                            <div class="text-sm break-words whitespace-pre-wrap">{{ $message->body }}</div>
                            <div class="text-xs {{ $message->sender_type->value === 'central' ? 'text-blue-100' : 'text-gray-500 dark:text-neutral-400' }} mt-1"
                                 x-data="{ timestamp: '{{ $message->created_at->toIso8601String() }}', formatted: '' }"
                                 x-init="formatted = formatRelativeTime(timestamp); setInterval(() => formatted = formatRelativeTime(timestamp), 60000)"
                                 x-text="formatted">
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex items-center justify-center h-full text-gray-500 dark:text-neutral-400">
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                            <p class="text-sm">{{ __('No hay mensajes aún') }}</p>
                            <p class="text-xs">{{ __('Esperando mensajes del tenant') }}</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Message input --}}
        <div class="border-t border-gray-200 dark:border-neutral-700 p-4 bg-white dark:bg-neutral-800" wire:ignore>
            <form wire:submit.prevent="sendMessage" x-data="messageInput()">
                <div class="flex gap-3 items-center">
                    <div class="flex-1">
                        <flux:textarea
                            wire:model.defer="newMessage"
                            rows="2"
                            placeholder="{{ __('Escribe tu respuesta...') }}"
                            class="resize-none"
                            @keydown.enter="handleKeyDown($event)" />
                    </div>
                    <button type="submit"
                            class="p-3 rounded-full bg-blue-500 hover:bg-blue-600 text-white transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                            x-bind:disabled="$wire.newMessage.trim() === ''">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z" />
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-500 dark:text-neutral-400 mt-2">{{ __('Enter para enviar, Shift+Enter para nueva línea') }}</p>
            </form>
        </div>

    </div>

    {{-- Panel informativo del entrenador --}}
    @if ($conversation->tenant)
        <div class="mt-6" wire:ignore>
            <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        Información del Entrenador
                    </h3>
                </div>

                {{-- Datos principales del entrenador --}}
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3 mb-6 pb-6 border-b border-gray-200 dark:border-neutral-700">
                    <div class="flex items-center gap-2">
                        <svg class="size-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="text-sm font-semibold text-gray-900 dark:text-neutral-100">{{ $conversation->tenant->name }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <svg class="size-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                        </svg>
                        <span class="text-sm text-gray-900 dark:text-neutral-100">{{ $conversation->tenant->mainDomain() }}</span>
                    </div>

                    @if ($conversation->tenant->data && isset($conversation->tenant->data['email']))
                        <div class="flex items-center gap-2">
                            <svg class="size-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <span class="text-sm text-gray-900 dark:text-neutral-100">{{ $conversation->tenant->data['email'] }}</span>
                        </div>
                    @endif

                    @if ($conversation->tenant->data && isset($conversation->tenant->data['phone']))
                        <div class="flex items-center gap-2">
                            <svg class="size-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <span class="text-sm text-gray-900 dark:text-neutral-100">{{ $conversation->tenant->data['phone'] }}</span>
                        </div>
                    @endif

                    <div class="flex items-center gap-2">
                        <svg class="size-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 6v6l4 2"></path>
                        </svg>
                        <span class="text-sm text-gray-900 dark:text-neutral-100">
                            Estado:
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $conversation->tenant->status->value === 'active' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                                @if ($conversation->tenant->status->value === 'active')
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                @endif
                                {{ ucfirst($conversation->tenant->status->value) }}
                            </span>
                        </span>
                    </div>
                </div>

                {{-- Plan comercial --}}
                @if ($conversation->tenant->plan)
                    <div class="mb-6 pb-6 border-b border-gray-200 dark:border-neutral-700">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-neutral-300 uppercase tracking-wide mb-3">Plan Comercial</h4>

                        <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                                <span class="font-medium text-gray-900 dark:text-neutral-100">{{ $conversation->tenant->plan->name }}</span>
                            </div>

                            @if ($conversation->tenant->plan->description)
                                <div class="flex items-center gap-2">
                                    <svg class="size-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                    </svg>
                                    <span class="text-sm text-gray-900 dark:text-neutral-100">{{ $conversation->tenant->plan->description }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Estadísticas de alumnos --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-neutral-300 uppercase tracking-wide mb-3">Estadísticas de Alumnos</h4>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-neutral-900/50 rounded-lg p-4 border border-gray-200 dark:border-neutral-700">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="size-4 text-gray-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span class="text-xs text-gray-500 dark:text-neutral-400">Total</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-neutral-100">{{ $tenantStats['totalStudents'] }}</p>
                        </div>

                        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-4 border border-emerald-200 dark:border-emerald-800">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="size-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <span class="text-xs text-emerald-600 dark:text-emerald-400">Activos</span>
                            </div>
                            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $tenantStats['activeStudents'] }}</p>
                        </div>

                        <div class="bg-gray-50 dark:bg-neutral-900/50 rounded-lg p-4 border border-gray-200 dark:border-neutral-700">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                                <span class="text-xs text-gray-500 dark:text-neutral-400">Inactivos</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-700 dark:text-neutral-300">{{ $tenantStats['inactiveStudents'] }}</p>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="size-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span class="text-xs text-blue-600 dark:text-blue-400">Prospectos</span>
                            </div>
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $tenantStats['prospectStudents'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

<script>
    function messageInput() {
        return {
            handleKeyDown(event) {
                // Si es Enter sin Shift, enviar el formulario
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    this.$el.closest('form').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                }
                // Si es Shift+Enter, permite el comportamiento por defecto (nueva línea)
            }
        }
    }

    function formatRelativeTime(timestamp) {
        const now = new Date();
        const messageDate = new Date(timestamp);
        const diffMs = now - messageDate;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        // Mensajes de las últimas 24 horas
        if (diffDays < 1) {
            if (diffMins < 1) return 'ahora';
            if (diffMins === 1) return 'hace 1 minuto';
            if (diffMins < 60) return `hace ${diffMins} minutos`;
            if (diffHours === 1) return 'hace 1 hora';
            return `hace ${diffHours} horas`;
        }

        // Más de 24 horas: mostrar fecha y hora
        return messageDate.toLocaleString('es-AR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
</script>
