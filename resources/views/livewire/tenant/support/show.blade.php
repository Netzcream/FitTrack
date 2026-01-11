<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="mb-6">
        <flux:heading size="xl" level="1">{{ __('Soporte Técnico') }}</flux:heading>
        <flux:subheading size="lg">{{ __('Contacta con el equipo de FitTrack para resolver tus dudas') }}</flux:subheading>
    </div>

    {{-- Chat Container --}}
    <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg overflow-hidden shadow-sm">

        {{-- Messages list --}}
        <div class="h-[500px] overflow-y-auto p-4 space-y-4"
             x-data="{
                 shouldScroll: true,
                 messageCount: {{ $messages->count() }},
                 scrollIfNeeded() {
                     const container = document.getElementById('messages-container-support');
                     if (container) {
                         const newCount = container.children.length;
                         if (newCount > this.messageCount && this.shouldScroll) {
                             this.messageCount = newCount;
                             this.$nextTick(() => { this.$el.scrollTop = this.$el.scrollHeight });
                         }
                     }
                 }
             }"
             x-init="$el.scrollTop = $el.scrollHeight; setInterval(() => scrollIfNeeded(), 3100)"
             @scroll="shouldScroll = Math.abs($el.scrollHeight - $el.scrollTop - $el.clientHeight) < 50"
             @message-sent.window="setTimeout(() => { $el.scrollTop = $el.scrollHeight }, 100)">

            <div wire:poll.3s class="space-y-4" id="messages-container-support">
                @forelse ($messages as $message)
                    <div class="flex {{ $message->sender_type->value === 'tenant' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[70%] rounded-lg px-4 py-2 shadow-sm {{ $message->sender_type->value === 'tenant' ? 'text-white' : 'bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100' }}" @if($message->sender_type->value === 'tenant') style="background-color: var(--ftt-color-base);" @endif>
                            <div class="text-sm break-words whitespace-pre-wrap">{{ $message->body }}</div>
                            <div class="text-xs {{ $message->sender_type->value === 'tenant' ? 'opacity-80' : 'text-gray-500 dark:text-neutral-400' }} mt-1">
                                {{ $message->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex items-center justify-center h-full text-gray-500 dark:text-neutral-400">
                        <div class="text-center">
                            <x-icons.lucide.message-circle class="w-12 h-12 mx-auto mb-2 text-gray-300 dark:text-neutral-600" />
                            <p class="text-sm">{{ __('No hay mensajes aún') }}</p>
                            <p class="text-xs">{{ __('Envía tu primer mensaje para contactar con soporte') }}</p>
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
                            placeholder="{{ __('Escribe tu mensaje...') }}"
                            class="resize-none"
                            @keydown.enter="handleKeyDown($event)" />
                    </div>
                    <button type="submit"
                            class="p-3 rounded-full text-white transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                            style="background-color: var(--ftt-color-base);"
                            onmouseover="this.style.backgroundColor='var(--ftt-color-base-bright)'"
                            onmouseout="this.style.backgroundColor='var(--ftt-color-base)'"
                            x-bind:disabled="$wire.newMessage.trim() === ''">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z" />
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-500 dark:text-neutral-400 mt-2">{{ __('Enter para enviar, Shift+Enter para nueva línea') }}</p>
            </form>
        </div>

        {{-- Link to Manuals --}}
        <div class="border-t border-gray-200 dark:border-neutral-700 px-4 py-3 bg-gray-50 dark:bg-neutral-900">
            <a href="{{ route('tenant.dashboard.manuals.index') }}" wire:navigate
               class="flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                <span>{{ __('Consultá nuestras guías y manuales') }}</span>
            </a>
        </div>

    </div>
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
</script>
