<div class="space-y-6">
    <x-student-header
        title="Mi perfil"
        subtitle="Actualiza tus datos personales y tu clave"
        icon="user"
        :student="$student" />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl p-6" style="border: 1px solid #e5e7eb; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Datos personales</h2>

            <form wire:submit.prevent="updateProfile" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model.defer="first_name" label="Nombre" type="text" required autocomplete="given-name" />
                    <flux:input wire:model.defer="last_name" label="Apellido" type="text" required autocomplete="family-name" />
                </div>

                <flux:input wire:model.defer="phone" label="Telefono" type="tel" autocomplete="tel" />

                <flux:input wire:model="email" label="Email" type="email" disabled />

                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-white font-semibold shadow-md" style="background-color: var(--ftt-color-base);">
                            Guardar
                        </button>
                    </div>

                    <x-tenant.action-message class="me-3" on="profile-updated">
                        Guardado
                    </x-tenant.action-message>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl p-6" style="border: 1px solid #e5e7eb; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Seguridad</h2>

            <form wire:submit.prevent="updatePassword" class="space-y-4">
                <flux:input
                    wire:model="current_password"
                    label="Clave actual"
                    type="password"
                    required
                    autocomplete="current-password"
                />
                <flux:input
                    wire:model="password"
                    label="Nueva clave"
                    type="password"
                    required
                    autocomplete="new-password"
                />
                <flux:input
                    wire:model="password_confirmation"
                    label="Confirmar clave"
                    type="password"
                    required
                    autocomplete="new-password"
                />

                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-white font-semibold shadow-md" style="background-color: var(--ftt-color-base);">
                            Actualizar clave
                        </button>
                    </div>

                    <x-tenant.action-message class="me-3" on="password-updated">
                        Guardado
                    </x-tenant.action-message>
                </div>
            </form>
        </div>
    </div>
</div>
