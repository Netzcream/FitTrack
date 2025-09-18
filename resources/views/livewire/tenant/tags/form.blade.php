<div class="flex items-start max-md:flex-col">
  <div class="flex-1 self-stretch w-full max-md:pt-6">
    <form wire:submit.prevent="save" class="space-y-6">
      <div>
        <flux:heading size="xl" level="1">
          {{ $editMode ? __('site.edit_tag') : __('site.new_tag') }}
        </flux:heading>
        <flux:subheading size="lg" class="mb-6">
          {{ __('site.tag_subheading') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
      </div>

      <div class="max-w-5xl space-y-8">
        {{-- Basics --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <flux:input wire:model.defer="name" label="{{ __('site.name') }}" required autocomplete="off" />
          <flux:input wire:model.defer="code" label="{{ __('site.code') }}" required autocomplete="off" />
        </div>
        @error('name') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
        @error('code') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror

        {{-- Color + Description --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <flux:input wire:model.defer="color" label="{{ __('site.color') }}" placeholder="#2563EB" />
          <flux:textarea rows="4" wire:model.defer="description" label="{{ __('site.description') }}"
            placeholder="{{ __('site.tag_description_placeholder') }}" />
        </div>
        @error('color') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
        @error('description') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror

        {{-- Active --}}
        <div class="flex items-center gap-3">
          <flux:checkbox wire:model.defer="is_active" />
          <flux:label>{{ __('site.active') }}</flux:label>
        </div>

        <div class="flex justify-end gap-4 pt-6 items-center">
          <x-tenant.action-message on="updated">
            {{ __('site.saved') }}
          </x-tenant.action-message>

          <flux:checkbox label="{{ __('site.back_list') }}" wire:model.live="back" />

          <flux:button as="a" variant="ghost"
            href="{{ route('tenant.dashboard.tags.index') }}">
            {{ $editMode ? __('site.back') : __('site.cancel') }}
          </flux:button>

          <flux:button type="submit" variant="primary">
            {{ $editMode ? __('site.update_tag') : __('site.create_tag') }}
          </flux:button>
        </div>
      </div>
    </form>
  </div>
</div>
