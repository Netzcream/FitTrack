<div class="max-w-2xl mx-auto py-8">
    <form wire:submit.prevent="save" class="space-y-10">

        <div>
            <flux:heading size="xl" level="1">
                {{ $editMode ? 'Editar rol' : 'Nuevo rol' }}
            </flux:heading>
            <flux:subheading size="lg" class="mb-6">
                {{ $editMode ? 'Modificá los datos del rol.' : 'Agregá un nuevo rol al sistema.' }}
            </flux:subheading>
            <flux:separator variant="subtle" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <flux:input wire:model.defer="name" label="Nombre" required autocomplete="off" />
            </div>

        </div>

        <div>
            <flux:label>Permisos</flux:label>
            <div class="flex flex-wrap gap-4">
                @foreach ($allPermissions as $id => $name)
                    <label class="inline-flex items-center gap-2 text-sm font-medium">
                        <input type="checkbox" value="{{ $name }}" wire:model="permissions"
                            class="form-radio accent-blue-600 dark:accent-blue-400 rounded focus:ring-2 focus:ring-blue-500">
                        <span>{{ ucfirst($name) }}</span>
                    </label>
                @endforeach
            </div>
            @error('role')
                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>


        <div class="flex justify-end gap-2 pt-6 items-center">
            <flux:button type="submit" variant="primary">
                {{ $editMode ? 'Actualizar rol' : 'Crear rol' }}
            </flux:button>
            <a href="{{ route('tenant.dashboard.roles.index') }}" class="flux:button">
                Cancelar
            </a>
        </div>
    </form>
</div>
