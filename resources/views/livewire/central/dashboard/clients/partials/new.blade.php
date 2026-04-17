<div>
    <flux:label for="name">{{ __('Nombre del entrenador') }}</flux:label>
    <flux:input class="mt-2" id="name" wire:model.defer="name" type="text" placeholder="Ingrese nombre" required
        maxlength="24" autofocus wire:blur="suggestSlug" />
    @error('name')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div>
    <flux:label for="slug">{{ __('Subdominio (slug)') }}</flux:label>

    <flux:input class="mt-2" id="slug" wire:model.defer="slug" type="text" inputmode="latin"
        placeholder="{{ __('Ej: acme') }}" required maxlength="32" {{-- cuando el usuario edita, normalizamos en updatedSlug() --}} />

    {{-- Ayuda + preview del dominio completo --}}
    <p class="mt-2 text-sm text-gray-600 dark:text-neutral-400">
        {{ __('Este valor define la URL del sitio.') }}
        <br>
        <span class="font-medium">{{ __('Vista previa:') }}</span>
        <span class="font-mono">{{ $this->fullDomain }}</span>
    </p>

    @error('slug')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div>
    <flux:label for="admin_email">{{ __('Mail del administrador') }}</flux:label>
    <flux:input class="mt-2" id="admin_email" wire:model.defer="admin_email" type="email"
        placeholder="Ingrese mail del administrador" required maxlength="255" />
    @error('admin_email')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div>
    <flux:label for="admin_password">{{ __('Clave del administrador') }}</flux:label>
    <flux:input class="mt-2" id="admin_password" wire:model.defer="admin_password" type="password"
        placeholder="Ingrese clave del administrador" required maxlength="255" autocomplete="new-password" />
    <p class="mt-2 text-sm text-gray-500 dark:text-neutral-400">
        {{ __('La clave será la que se utilice para ingresar como administrador del sitio.') }}
    </p>
    @error('admin_password')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-4">
    <flux:checkbox
        id="database_already_exists"
        wire:model.defer="database_already_exists"
        label="La base del tenant ya existe"
    />

    <p class="mt-2 text-sm text-gray-600 dark:text-neutral-400">
        Si est&aacute; marcada, el sistema no intentar&aacute; crear ni eliminar la base de datos del tenant.
        En ese caso ten&eacute;s que indicar el nombre exacto de la base ya creada en cPanel.
    </p>

    @if ($database_already_exists)
        <div class="mt-3">
            <flux:label for="database_name">{{ __('Base de datos existente') }}</flux:label>
            <flux:input
                class="mt-2"
                id="database_name"
                wire:model.defer="database_name"
                type="text"
                placeholder="Ej: usuario_fittrack_demo"
                maxlength="64"
            />
            <p class="mt-2 text-sm text-gray-600 dark:text-neutral-400">
                Ingres&aacute; el nombre real de la base, incluyendo el prefijo del usuario de cPanel.
            </p>
            @error('database_name')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    @endif
</div>

@if (config('demo.enabled'))
    <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-100">
        El modo demo est&aacute; habilitado. No se permite crear nuevos tenants mientras esta opci&oacute;n est&eacute; activa.
    </div>
@endif
