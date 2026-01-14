<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="xl" level="1">
                    {{ $editMode ? __('Editar pago') : __('Nuevo pago') }}
                </flux:heading>
                <flux:subheading size="lg" class="mb-6">
                    {{ __('Creá un pago para un alumno. El método será elegido luego por el alumno.') }}
                </flux:subheading>
                <flux:separator variant="subtle" />
            </div>

            <div class="max-w-4xl space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:select wire:model.live="student_id" label="{{ __('Alumno') }}" required>
                        <option value="">{{ __('Seleccionar alumno') }}</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </option>
                        @endforeach
                    </flux:select>

                    <div class="space-y-2">
                        <flux:input wire:model.defer="amount" label="{{ __('Monto ($)') }}" type="number" step="0.01"
                            :disabled="$autoAmount" required />
                        <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-neutral-400">
                            <flux:checkbox wire:model.live="autoAmount" size="sm" />
                            <span>{{ __('Usar monto del plan comercial si existe. Destilda para editar manualmente.') }}</span>
                        </div>
                    </div>
                </div>

                <flux:textarea wire:model.defer="notes" label="{{ __('Notas') }}" placeholder="Detalles opcionales..." />

                <div class="flex justify-end gap-4 pt-6 items-center">
                    <x-tenant.action-message on="updated">{{ __('Guardado') }}</x-tenant.action-message>

                    <flux:checkbox label="{{ __('Volver al listado') }}" wire:model.live="back" />

                    <flux:button as="a" variant="ghost" href="{{ route('tenant.dashboard.payments.index') }}">
                        {{ __('Cancelar') }}
                    </flux:button>

                    <flux:button type="submit" variant="primary">
                        {{ $editMode ? __('Actualizar pago') : __('Crear pago') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
