<div class="space-y-8">
    <div>
        <flux:heading size="xl" level="1">
            {{ __('Plantilla de ejercicio') }}
        </flux:heading>
        <flux:subheading class="mb-6">
            {{ __('Completá los datos y construí la estructura en la misma pantalla.') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <form wire:submit.prevent="saveHeader" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:input wire:model.defer="name" label="{{ __('Nombre') }}" required />
            <flux:input wire:model.defer="code" label="{{ __('Código') }}" required />

            <div>
                <flux:label class="text-xs">{{ __('Estado') }}</flux:label>
                <flux:select wire:model.defer="status">
                    <option value="draft">{{ __('Borrador') }}</option>
                    <option value="published">{{ __('Publicado') }}</option>
                    <option value="archived">{{ __('Archivado') }}</option>
                </flux:select>
            </div>

            <div class="flex items-center gap-3">
                <flux:switch wire:model.defer="is_public" />
                <span class="text-sm">{{ __('Hacer pública (visible en librería)') }}</span>
            </div>

            <div class="md:col-span-2">
                <flux:textarea wire:model.defer="description" label="{{ __('Descripción (opcional)') }}" rows="3" />
            </div>
        </div>

        <div class="flex gap-3">
            <flux:button type="submit">{{ __('Guardar datos') }}</flux:button>
            <a href="{{ route('tenant.dashboard.exercises.plans.templates.index') }}">
                <flux:button variant="ghost">{{ __('Volver al listado') }}</flux:button>
            </a>
        </div>
    </form>

    {{-- Separador visual --}}
    <flux:separator variant="subtle" />

    {{-- Builder embebido (reutilizamos TODO tu componente actual) --}}
    @livewire(
        \App\Livewire\Tenant\Exercises\Plans\Templates\Builder\Index::class,
        ['template' => $this->template],
        key('builder-'.$this->template->id)
    )
</div>
