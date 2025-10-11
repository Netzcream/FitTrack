<div class="flex items-start max-md:flex-col">
  <div class="flex-1 self-stretch w-full max-md:pt-6">

    <x-simple-form
        :submit="'save'"
        :edit-mode="$editMode"
        :back-route="route('tenant.dashboard.users.index')"
        :back-model="'back'"

        :title-new="__('users.new_title')"
        :title-edit="__('users.edit_title')"
        :sub-new="__('users.new_subheading')"
        :sub-edit="__('users.edit_subheading')"
        :create-label="__('users.create_button')"
        :update-label="__('users.update_button')"
        :back-label="__('site.back')"
        :back-list-label="__('site.back_list')"
    >
        {{-- SLOT: inputs --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:input wire:model.defer="name"  label="{{ __('users.name') }}"  required autocomplete="off" />
            <flux:input wire:model.defer="email" label="{{ __('users.email') }}" required autocomplete="off" type="email" />
        </div>
        @error('name')  <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
        @error('email') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror

        <div>
            <flux:label class="text-xs">{{ __('users.role') }}</flux:label>
            <div class="flex flex-wrap gap-4 mt-2">
                @foreach ($allRoles as $id => $name)
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-800 dark:text-neutral-200">
                        <input type="radio" value="{{ $name }}" wire:model="role"
                               class="form-radio accent-blue-600 dark:accent-blue-400 rounded focus:ring-2 focus:ring-blue-500">
                        <span>{{ ucfirst($name) }}</span>
                    </label>
                @endforeach
            </div>
            @error('role') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:input wire:model.defer="password"
                label="{{ $editMode ? __('users.new_password') : __('users.password') }}"
                type="password" autocomplete="new-password" :required="!$editMode" />
            <flux:input wire:model.defer="password_confirmation"
                label="{{ __('users.password_confirmation') }}"
                type="password" autocomplete="new-password" :required="!$editMode" />
        </div>

        {{-- Opcional: acciones extra (header) --}}
        @slot('actions')
            {{-- ejemplo: ayuda contextual --}}
            {{-- <flux:button size="sm" as="a" variant="ghost" href="{{ route('docs.form') }}">AYUDA</flux:button> --}}
        @endslot

        {{-- Opcional: acciones extra (footer) --}}
        @slot('footerActions')
            {{-- ejemplo: botón secundario --}}
            {{-- <flux:button size="sm" variant="ghost">Acción secundaria</flux:button> --}}
        @endslot
    </x-simple-form>

  </div>
</div>
