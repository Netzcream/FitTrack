<div>
    <flux:label for="name">{{ __('Nombre del cliente') }}</flux:label>
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
    <flux:input class="mt-2" id="admin_email" wire:model="admin_email" type="email"
        placeholder="Ingrese mail del administrador" required maxlength="255" />
    @error('admin_email')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div>
    <flux:label for="admin_password">{{ __('Clave del administrador') }}</flux:label>
    <flux:input class="mt-2" id="admin_password" wire:model="admin_password" type="text"
        placeholder="Ingrese clave del administrador" required maxlength="255" />
    <p class="mt-2 text-sm text-gray-500 dark:text-neutral-400">
        {{ __('La clave será la que se utilice para ingresar como administrador del sitio.') }}
    </p>
    @error('admin_password')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
