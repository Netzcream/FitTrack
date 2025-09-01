<div class="max-w-2xl mx-auto py-8">
    <form wire:submit.prevent="save" class="space-y-10">

        <div>
            <flux:heading size="xl" level="1">
                {{ $editMode ? 'Editar usuario' : 'Nuevo usuario' }}
            </flux:heading>
            <flux:subheading size="lg" class="mb-6">
                {{ $editMode ? 'Modificá los datos del usuario.' : 'Agregá un nuevo usuario al sistema.' }}
            </flux:subheading>
            <flux:separator variant="subtle" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <flux:input wire:model.defer="name" label="Nombre" required autocomplete="off" />
            </div>
            <div>
                <flux:input wire:model.defer="email" label="Email" required autocomplete="off" type="email" />
            </div>
        </div>

        <div>
            <flux:label>Rol</flux:label>
            <div class="flex flex-wrap gap-4">
                @foreach ($allRoles as $id => $name)
                    <label class="inline-flex items-center gap-2 text-sm font-medium">
                        <input type="radio" value="{{ $name }}" wire:model="role"
                            class="form-radio accent-blue-600 dark:accent-blue-400 rounded focus:ring-2 focus:ring-blue-500">
                        <span>{{ ucfirst($name) }}</span>
                    </label>
                @endforeach
            </div>
            @error('role')
                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <flux:input wire:model.defer="password"
                    label="{{ $editMode ? 'Nueva contraseña (dejar vacío para no cambiar)' : 'Contraseña' }}"
                    type="password" autocomplete="new-password" :required="!$editMode" />
            </div>
            <div>
                <flux:input wire:model.defer="password_confirmation" label="Repetir contraseña" type="password"
                    autocomplete="new-password" :required="!$editMode" />
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-6 items-center">
            <flux:button type="submit" variant="primary">
                {{ $editMode ? 'Actualizar usuario' : 'Crear usuario' }}
            </flux:button>
            <a href="{{ route('tenant.dashboard.users.index') }}" class="flux:button">
                Cancelar
            </a>
        </div>
    </form>
</div>
