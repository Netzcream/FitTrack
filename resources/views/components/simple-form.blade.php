@props([
    'submit'        => 'save',
    'editMode'      => false,
    'backRoute'     => null,

    'titleNew'      => __('entity.new_title'),
    'titleEdit'     => __('entity.edit_title'),
    'subNew'        => __('entity.new_subheading'),
    'subEdit'       => __('entity.edit_subheading'),
    'createLabel'   => __('entity.create_button'),
    'updateLabel'   => __('entity.update_button'),
    'backLabel'     => __('site.back'),
    'backListLabel' => __('site.back_list'),

    'backModel'     => 'back',
    'maxWidth'      => 'max-w-3xl',
    'showBack'      => true,
    'showBackCheck' => true,

    // 🔹 NUEVO
    'showSavedMessage' => true,
    'savedLabel'       => __('site.saved'),
])

<form wire:submit.prevent="{{ $submit }}" class="space-y-6">
    <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
        <div class="flex items-center justify-between gap-4 {{ $maxWidth }}">
            <div>
                <flux:heading size="xl" level="1">
                    {{ $editMode ? $titleEdit : $titleNew }}
                </flux:heading>
                <flux:subheading size="lg" class="mb-6">
                    {{ $editMode ? $subEdit : $subNew }}
                </flux:subheading>
            </div>

            <div class="flex items-center gap-3">
                {{-- 🔹 action message ANTES del checkbox --}}
                @if($showSavedMessage)
                    <x-tenant.action-message on="saved">
                        {{ $savedLabel }}
                    </x-tenant.action-message>
                @endif

                @if($showBackCheck)
                    <flux:checkbox size="sm" label="{{ $backListLabel }}" wire:model.live="{{ $backModel }}" />
                @endif

                @if($showBack && $backRoute)
                    <flux:button as="a" variant="ghost" href="{{ $backRoute }}" size="sm">
                        {{ $backLabel }}
                    </flux:button>
                @endif

                <flux:button type="submit" size="sm">
                    {{ $editMode ? $updateLabel : $createLabel }}
                </flux:button>

                @isset($actions)
                    {{ $actions }}
                @endisset
            </div>
        </div>
        <flux:separator variant="subtle" class="mt-2" />
    </div>

    <div class="{{ $maxWidth }} space-y-4 pt-2">
        {{ $slot }}
    </div>

    <div class="pt-6 {{ $maxWidth }}">
        <div class="flex justify-end gap-3 items-center text-sm opacity-80">
            {{-- 🔹 action message ANTES del checkbox (footer) --}}
            @if($showSavedMessage)
                <x-tenant.action-message on="saved">
                    {{ $savedLabel }}
                </x-tenant.action-message>
            @endif

            @if($showBackCheck)
                <flux:checkbox size="sm" label="{{ $backListLabel }}" wire:model.live="{{ $backModel }}" />
            @endif

            @if($showBack && $backRoute)
                <flux:button as="a" variant="ghost" href="{{ $backRoute }}" size="sm">
                    {{ $backLabel }}
                </flux:button>
            @endif

            <flux:button type="submit" size="sm">
                {{ $editMode ? $updateLabel : $createLabel }}
            </flux:button>

            @isset($footerActions)
                {{ $footerActions }}
            @endisset
        </div>
    </div>

    <flux:separator variant="subtle" class="mt-8" />
</form>
