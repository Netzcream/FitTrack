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

        <div>
            <flux:label class="text-xs">{{ __('users.extra_permissions') }}</flux:label>
            <p class="mt-1 text-xs text-gray-500 dark:text-neutral-400">
                {{ __('users.extra_permissions_help') }}
            </p>

            @if (count($selectedDirectPermissions) > 0)
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($selectedDirectPermissions as $permission)
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            <span>{{ ucfirst($permission['name']) }}</span>
                            <button
                                type="button"
                                wire:click="removeDirectPermission({{ $permission['id'] }})"
                                class="inline-flex h-4 w-4 items-center justify-center rounded-full text-zinc-500 hover:bg-zinc-200 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-700 dark:hover:text-white"
                                aria-label="{{ __('users.extra_permissions_remove') }}"
                            >
                                x
                            </button>
                        </span>
                    @endforeach
                </div>
            @else
                <p class="mt-3 text-xs text-gray-500 dark:text-neutral-400">
                    {{ __('users.extra_permissions_empty') }}
                </p>
            @endif

            <div class="mt-3">
                <flux:input
                    wire:model.live.debounce.250ms="permissionSearch"
                    :label="__('users.extra_permissions_search')"
                    :placeholder="__('users.extra_permissions_search_placeholder')"
                    autocomplete="off"
                />
            </div>

            <div class="mt-2 max-h-52 overflow-y-auto rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                @forelse ($permissionSuggestions as $permissionId => $permissionName)
                    <button
                        type="button"
                        wire:click="addDirectPermission({{ $permissionId }})"
                        class="flex w-full items-center justify-between px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        <span>{{ ucfirst($permissionName) }}</span>
                        <span class="text-[11px] uppercase tracking-wide text-zinc-400">{{ __('users.extra_permissions_add') }}</span>
                    </button>
                @empty
                    <p class="px-3 py-2 text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('users.extra_permissions_no_results') }}
                    </p>
                @endforelse
            </div>

            @error('directPermissionIds') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
            @error('directPermissionIds.*') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
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
