<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6 space-y-6">
        {{-- Header --}}
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('Detalle del contacto recibido') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">{{ __('Información del contacto desde la web') }}</flux:subheading>
                </div>

                <flux:button as="a" wire:navigate href="{{ route('tenant.dashboard.contacts.index') }}" variant="ghost" icon="arrow-left">
                    {{ __('Volver') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>

        {{-- Contenido --}}
        <div class="max-w-3xl space-y-6">
            {{-- Datos de contacto --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <flux:label class="text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase">{{ __('Nombre') }}</flux:label>
                    <div class="mt-1 text-base font-semibold text-gray-900 dark:text-neutral-100">
                        {{ $contact->name }}
                    </div>
                </div>

                <div>
                    <flux:label class="text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase">{{ __('Email') }}</flux:label>
                    <div class="mt-1 text-base text-gray-700 dark:text-neutral-300">
                        <a href="mailto:{{ $contact->email }}" class="hover:underline text-[var(--ftt-color-base)]">{{ $contact->email }}</a>
                    </div>
                </div>

                <div>
                    <flux:label class="text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase">{{ __('Teléfono') }}</flux:label>
                    <div class="mt-1 text-base text-gray-700 dark:text-neutral-300">
                        @if($contact->phone)
                            <a href="tel:{{ $contact->phone }}" class="hover:underline text-gray-900 dark:text-neutral-100">{{ $contact->phone }}</a>
                        @else
                            <span class="text-gray-400 dark:text-neutral-500">-</span>
                        @endif
                    </div>
                </div>

                <div>
                    <flux:label class="text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase">{{ __('Fecha de contacto') }}</flux:label>
                    <div class="mt-1 text-base text-gray-700 dark:text-neutral-300">
                        {{ $contact->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>

            <flux:separator variant="subtle" />

            {{-- Mensaje --}}
            <div>
                <flux:label class="text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase mb-2">{{ __('Mensaje enviado') }}</flux:label>
                <div class="mt-2 bg-gray-50 dark:bg-neutral-800/50 rounded-lg p-4 text-sm text-gray-900 dark:text-neutral-100 whitespace-pre-line border border-gray-200 dark:border-neutral-700">{{ $contact->message }}</div>
            </div>
        </div>
    </div>
</div>
