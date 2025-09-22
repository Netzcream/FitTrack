<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6">
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('exercise.exercise_planes') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">
                        {{ __('exercise.exercise_planes_subheading') }}
                    </flux:subheading>
                </div>
                <flux:button as="a" href="{{ route('tenant.dashboard.exercise.exercise-planes.create') }}"
                    variant="primary" icon="plus">
                    {{ __('exercise.new_exercise_plane') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>

        <section class="w-full">
            <x-data-table :pagination="$exercisePlanes">
                <x-slot name="filters">
                    <div class="flex flex-wrap gap-4 w-full items-end">
                        <div class="max-w-[260px] flex-1">
                            <flux:label class="text-xs">{{ __('exercise.search') }}</flux:label>
                            <flux:input size="sm" wire:model.live.debounce.400ms="search"
                                placeholder="{{ __('exercise.search_placeholder') }}" class="w-full" />
                        </div>

                        <div class="max-w-[220px]">
                            <flux:label class="text-xs">{{ __('exercise.status') }}</flux:label>
                            <flux:select wire:model.live="status" size="sm" class="w-full">
                                <option value="">{{__('site.select')}}</option>
                                <option value="{{ \App\Models\Tenant\Exercise\ExercisePlane::STATUS_DRAFT }}">
                                    {{ __('exercise.status_draft') }}
                                </option>
                                <option value="{{ \App\Models\Tenant\Exercise\ExercisePlane::STATUS_PUBLISHED }}">
                                    {{ __('exercise.status_published') }}
                                </option>
                                <option value="{{ \App\Models\Tenant\Exercise\ExercisePlane::STATUS_ARCHIVED }}">
                                    {{ __('exercise.status_archived') }}
                                </option>
                            </flux:select>
                        </div>
                    </div>
                </x-slot>

                <x-slot name="head">
                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase text-left">{{ __('exercise.order') }}</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">{{ __('exercise.name') }}</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">{{ __('exercise.code') }}</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">{{ __('exercise.status') }}</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-end">{{ __('exercise.actions') }}</th>
                </x-slot>

                @forelse ($exercisePlanes as $plane)
                    <tr>
                        <td class="align-top px-4 py-4">
                            <div class="flex flex-col items-start gap-1">
                                <flux:button size="xs" variant="ghost" icon="chevron-up"
                                    title="{{ __('exercise.move_up') }}" wire:click="moveUp({{ $plane->id }})"
                                    :disabled="($plane->order === null) || ($plane->order <= $minOrder)" />
                                <flux:button size="xs" variant="ghost" icon="chevron-down"
                                    title="{{ __('exercise.move_down') }}" wire:click="moveDown({{ $plane->id }})"
                                    :disabled="($plane->order === null) || ($plane->order >= $maxOrder)" />
                            </div>
                        </td>
                        <td class="px-6 py-4">{{ $plane->name }}</td>
                        <td class="px-6 py-4">{{ $plane->code }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded text-xs">
                                {{ __("exercise.status_{$plane->status}") }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-end">
                            <flux:button wire:navigate size="sm" href="{{ route('tenant.dashboard.exercise.exercise-planes.edit', $plane) }}">
                                {{ __('exercise.edit') }}
                            </flux:button>
                            <flux:modal.trigger name="confirm-delete-exercise-plane">
                                <flux:button size="sm" variant="ghost" wire:click="confirmDelete({{ $plane->id }})">
                                    {{ __('exercise.delete') }}
                                </flux:button>
                            </flux:modal.trigger>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            {{ __('exercise.no_exercise_plane_found') }}
                        </td>
                    </tr>
                @endforelse

                <x-slot name="modal">
                    <flux:modal name="confirm-delete-exercise-plane" class="min-w-[22rem]" x-data
                        @exercise-plane-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-exercise-plane' })">
                        <div class="space-y-6">
                            <flux:heading size="lg">{{ __('exercise.delete_exercise_plane_title') }}</flux:heading>
                            <flux:text>{{ __('exercise.delete_exercise_plane_message') }}</flux:text>
                            <div class="flex gap-2">
                                <flux:spacer />
                                <flux:modal.close><flux:button variant="ghost">{{ __('exercise.cancel') }}</flux:button></flux:modal.close>
                                <flux:button wire:click="delete" variant="danger">{{ __('exercise.confirm_delete') }}</flux:button>
                            </div>
                        </div>
                    </flux:modal>
                </x-slot>
            </x-data-table>
        </section>
    </div>
</div>
