<div class="max-w-2xl mx-auto mt-10">
    <div class="bg-white dark:bg-neutral-900 shadow rounded-2xl p-8">
        <flux:heading size="xl" level="1" class="mb-4">{{ __('Detalle del contacto recibido') }}</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <flux:label>{{ __('Nombre') }}</flux:label>
                <div class="text-lg font-semibold text-gray-900 dark:text-neutral-100">
                    {{ $contact->name }}
                </div>
            </div>
            <div>
                <flux:label>{{ __('Email') }}</flux:label>
                <div class="text-lg text-gray-700 dark:text-neutral-300">
                    <a href="mailto:{{ $contact->email }}" class="hover:underline text-blue-700 dark:text-blue-400">{{ $contact->email }}</a>
                </div>
            </div>
            <div>
                <flux:label>{{ __('Teléfono') }}</flux:label>
                <div class="text-lg text-gray-700 dark:text-neutral-300">
                    <a href="tel:{{ $contact->phone }}" class="hover:underline">{{ $contact->phone }}</a>
                </div>
            </div>
            <div>
                <flux:label>{{ __('Fecha de contacto') }}</flux:label>
                <div class="text-lg text-gray-700 dark:text-neutral-300">
                    {{ $contact->created_at->format('d/m/Y H:i') }}
                </div>
            </div>

        </div>

        <flux:label>{{ __('Mensaje enviado') }}</flux:label>
        <div class="mt-2 bg-gray-100 dark:bg-white/10 rounded-xl p-4 text-base text-gray-900 dark:text-neutral-100 whitespace-pre-line shadow-inner mb-6">{{ $contact->message }}</div>

        <div class="flex justify-end gap-2 items-center">
            <a href="{{ route('tenant.dashboard.contacts.index') }}" class="flux:button">
                {{ __('Volver a contactos') }}
            </a>
        </div>
    </div>
</div>
