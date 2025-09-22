<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="xl" level="1">
                    {{ $editMode ? __('exercise.edit_muscle_group') : __('exercise.new_muscle_group') }}
                </flux:heading>
                <flux:subheading size="lg" class="mb-6">
                    {{ __('exercise.muscle_group_subheading') }}
                </flux:subheading>
                <flux:separator variant="subtle" />
            </div>

            <div class="max-w-5xl space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input wire:model.defer="name" label="{{ __('exercise.name') }}" required />
                    <flux:input wire:model.defer="code" label="{{ __('exercise.code') }}" required />
                </div>
                @error('name') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
                @error('code') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror

                <flux:textarea wire:model.defer="description" label="{{ __('exercise.description') }}" />

                <div>
                    <flux:label class="text-xs">{{ __('exercise.status') }}</flux:label>
                    <flux:select wire:model.defer="status">
                        <option value="{{ \App\Models\Tenant\Exercise\MuscleGroup::STATUS_DRAFT }}">
                            {{ __('exercise.status_draft') }}
                        </option>
                        <option value="{{ \App\Models\Tenant\Exercise\MuscleGroup::STATUS_PUBLISHED }}">
                            {{ __('exercise.status_published') }}
                        </option>
                        <option value="{{ \App\Models\Tenant\Exercise\MuscleGroup::STATUS_ARCHIVED }}">
                            {{ __('exercise.status_archived') }}
                        </option>
                    </flux:select>
                    @error('status') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="flex justify-end gap-4 pt-6 items-center">
                    <x-tenant.action-message on="updated">
                        {{ __('exercise.saved') }}
                    </x-tenant.action-message>

                    <flux:checkbox label="{{ __('exercise.back_list') }}" wire:model.live="back" />

                    <flux:button as="a" variant="ghost"
                        href="{{ route('tenant.dashboard.exercise.muscle-groups.index') }}">
                        {{ $editMode ? __('exercise.back') : __('exercise.cancel') }}
                    </flux:button>

                    <flux:button type="submit" variant="primary">
                        {{ $editMode ? __('exercise.update_muscle_group') : __('exercise.create_muscle_group') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
