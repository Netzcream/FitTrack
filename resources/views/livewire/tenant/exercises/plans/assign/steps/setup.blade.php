<div class="space-y-6">
    <flux:heading size="lg">{{ __('Paso 3 — Configurar parámetros del plan') }}</flux:heading>
    <flux:subheading>
        {{ __('Definí la fecha de inicio y un nombre opcional para identificar el plan instanciado.') }}
    </flux:subheading>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <flux:input
            type="date"
            label="{{ __('Fecha de inicio') }}"
            wire:model="state.start_date"
        />

        <flux:input
            label="{{ __('Nombre del plan (opcional)') }}"
            placeholder="{{ __('Por ejemplo: Plan Hipertrofia Octubre') }}"
            wire:model="state.name_override"
        />
    </div>

    <flux:textarea
        rows="3"
        label="{{ __('Comentarios internos (opcional)') }}"
        wire:model.defer="state.comments"
    />

    <div class="flex justify-between mt-6">
        <flux:button variant="subtle" wire:click="back">
            ← {{ __('Volver') }}
        </flux:button>

        <flux:button variant="primary" wire:click="next">
            {{ __('Continuar') }} →
        </flux:button>
    </div>
</div>
