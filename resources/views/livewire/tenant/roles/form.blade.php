<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <x-simple-form submit="save" :edit-mode="$editMode" :back-route="route('tenant.dashboard.roles.index')" :back-model="'back'" :max-width="'max-w-3xl'"
            {{-- i18n/labels (podés cambiarlos a __('roles.*') cuando tengas las traducciones) --}} title-new="Nuevo rol" title-edit="Editar rol" sub-new="Agregá un nuevo rol al sistema."
            sub-edit="Modificá los datos del rol." create-label="Crear rol" update-label="Actualizar rol"
            back-label="Cancelar" back-list-label="Volver al listado">
            {{-- SLOT: inputs --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <flux:input wire:model.defer="name" label="Nombre" required autocomplete="off" />
                    @error('name')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div>
                <flux:label>Permisos</flux:label>
                <div class="flex flex-wrap gap-4 mt-2">
                    @foreach ($allPermissions as $id => $permName)
                        <label
                            class="inline-flex items-center gap-2 text-sm font-medium text-gray-800 dark:text-neutral-200">
                            <input type="checkbox" value="{{ $permName }}" wire:model="permissions"
                                class="form-checkbox accent-blue-600 dark:accent-blue-400 rounded focus:ring-2 focus:ring-blue-500">
                            <span>{{ ucfirst($permName) }}</span>
                        </label>
                    @endforeach
                </div>
                @error('permissions')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
                @error('permissions.*')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>


        </x-simple-form>
    </div>
</div>
