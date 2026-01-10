<div class="space-y-6">
    <form wire:submit.prevent="save" class="space-y-6">

        {{-- Nombre --}}
        <div>
            <flux:input
                wire:model="name"
                label="Nombre del Ejercicio"
                placeholder="Ej: Press de banca inclinado"
                required />
        </div>

        {{-- Categoría y Nivel --}}
        <div class="grid grid-cols-2 gap-4">
            <flux:select
                wire:model="category"
                label="Categoría"
                placeholder="Seleccionar..."
                required>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </flux:select>

            <flux:select
                wire:model="level"
                label="Nivel"
                required>
                <option value="beginner">Principiante</option>
                <option value="intermediate">Intermedio</option>
                <option value="advanced">Avanzado</option>
            </flux:select>
        </div>

        {{-- Equipo --}}
        <div>
            <flux:select
                wire:model="equipment"
                label="Equipo"
                placeholder="Seleccionar..."
                required>
                @foreach($equipments as $equip)
                    <option value="{{ $equip }}">{{ $equip }}</option>
                @endforeach
            </flux:select>
        </div>

        {{-- Descripción --}}
        <div>
            <flux:textarea
                wire:model="description"
                label="Descripción (opcional)"
                rows="2"
                placeholder="Breve descripción del ejercicio..." />
        </div>

        {{-- Botones --}}
        <div class="flex justify-end gap-3 pt-2">
            <flux:modal.close>
                <flux:button variant="ghost" type="button">
                    Cancelar
                </flux:button>
            </flux:modal.close>
            <flux:button wire:click="save" variant="primary" type="button">
                Crear Ejercicio
            </flux:button>
        </div>

    </form>
</div>
