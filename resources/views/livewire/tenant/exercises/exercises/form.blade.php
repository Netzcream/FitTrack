<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-8">
            <div>
                <flux:heading size="xl" level="1">
                    {{ $editMode ? __('exercise.edit_exercise') : __('exercise.new_exercise') }}
                </flux:heading>
                <flux:subheading size="lg" class="mb-6">
                    {{ __('exercise.exercise_form_subheading') }}
                </flux:subheading>
                <flux:separator variant="subtle" />
            </div>

            <div class="max-w-6xl space-y-10">
                {{-- Básicos --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input wire:model.defer="name" label="{{ __('exercise.name') }}" required />
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <flux:input wire:model.defer="code" label="{{ __('exercise.code') }}" required />
                        </div>
                        <flux:button type="button" size="sm" variant="ghost" wire:click="generateCodeFromName">
                            {{ __('exercise.generate_code') }}
                        </flux:button>
                    </div>
                </div>
                @error('name')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
                @error('code')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror

                {{-- Relaciones + Flags --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <flux:label class="text-xs">{{ __('exercise.level') }}</flux:label>
                        <flux:select wire:model.defer="exercise_level_id">
                            <option value=""></option>
                            @foreach ($levels as $l)
                                <option value="{{ $l->id }}">{{ $l->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:label class="text-xs">{{ __('exercise.movement_pattern') }}</flux:label>
                        <flux:select wire:model.defer="movement_pattern_id">
                            <option value=""></option>
                            @foreach ($patterns as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:label class="text-xs">{{ __('exercise.exercise_plane') }}</flux:label>
                        <flux:select wire:model.defer="exercise_plane_id">
                            <option value=""></option>
                            @foreach ($planes as $pl)
                                <option value="{{ $pl->id }}">{{ $pl->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex items-center gap-3">
                        <flux:checkbox wire:model.defer="unilateral" />
                        <flux:label>{{ __('exercise.unilateral') }}</flux:label>
                    </div>
                    <div class="flex items-center gap-3">
                        <flux:checkbox wire:model.defer="external_load" />
                        <flux:label>{{ __('exercise.external_load') }}</flux:label>
                    </div>
                    <div>
                        <flux:label class="text-xs">{{ __('exercise.status') }}</flux:label>
                        <flux:select wire:model.defer="status">
                            <option value="{{ \App\Models\Tenant\Exercise\Exercise::STATUS_DRAFT }}">
                                {{ __('exercise.status_draft') }}</option>
                            <option value="{{ \App\Models\Tenant\Exercise\Exercise::STATUS_PUBLISHED }}">
                                {{ __('exercise.status_published') }}</option>
                            <option value="{{ \App\Models\Tenant\Exercise\Exercise::STATUS_ARCHIVED }}">
                                {{ __('exercise.status_archived') }}</option>
                        </flux:select>
                    </div>
                </div>

                {{-- Modalidad por defecto + Prescripción (libre) --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <flux:label class="text-xs">{{ __('exercise.default_modality') }}</flux:label>
                        <flux:select wire:model.defer="default_modality">
                            @php $mods = [\App\Models\Tenant\Exercise\Exercise::MOD_REPS, \App\Models\Tenant\Exercise\Exercise::MOD_TIME, \App\Models\Tenant\Exercise\Exercise::MOD_DISTANCE, \App\Models\Tenant\Exercise\Exercise::MOD_CALORIES, \App\Models\Tenant\Exercise\Exercise::MOD_RPE, \App\Models\Tenant\Exercise\Exercise::MOD_LOAD_ONLY, \App\Models\Tenant\Exercise\Exercise::MOD_TEMPO_ONLY]; @endphp
                            @foreach ($mods as $m)
                                <option value="{{ $m }}">{{ __("exercise.mod_{$m}") }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div class="md:col-span-2">
                        <flux:textarea wire:model.defer="default_prescription.notes"
                            label="{{ __('exercise.default_prescription') }}" placeholder=""></flux:textarea>
                    </div>
                </div>

                {{-- Texto técnico --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:input wire:model.defer="tempo_notation" label="{{ __('exercise.tempo_notation') }}" />
                    <flux:input wire:model.defer="range_of_motion_notes"
                        label="{{ __('exercise.range_of_motion_notes') }}" />
                    <flux:input wire:model.defer="equipment_notes" label="{{ __('exercise.equipment_notes') }}" />
                </div>

                {{-- Repeaters: steps / cues / mistakes --}}
                <div class="space-y-6">
                    <div>
                        <flux:label class="text-xs">{{ __('exercise.setup_steps') }}</flux:label>
                        <div class="space-y-2">
                            @foreach ($setup_steps as $i => $step)
                                <div class="flex gap-2">
                                    <flux:input class="flex-1" wire:model.defer="setup_steps.{{ $i }}" />
                                    <flux:button type="button" variant="ghost" size="sm"
                                        wire:click="removeStep({{ $i }})">{{ __('exercise.remove') }}
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>
                        <flux:button type="button" size="sm" variant="ghost" wire:click="addStep">
                            {{ __('exercise.add_step') }}</flux:button>
                    </div>

                    <div>
                        <flux:label class="text-xs">{{ __('exercise.execution_cues') }}</flux:label>
                        <div class="space-y-2">
                            @foreach ($execution_cues as $i => $cue)
                                <div class="flex gap-2">
                                    <flux:input class="flex-1" wire:model.defer="execution_cues.{{ $i }}" />
                                    <flux:button type="button" variant="ghost" size="sm"
                                        wire:click="removeCue({{ $i }})">{{ __('exercise.remove') }}
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>
                        <flux:button type="button" size="sm" variant="ghost" wire:click="addCue">
                            {{ __('exercise.add_cue') }}</flux:button>
                    </div>

                    <div>
                        <flux:label class="text-xs">{{ __('exercise.common_mistakes') }}</flux:label>
                        <div class="space-y-2">
                            @foreach ($common_mistakes as $i => $m)
                                <div class="flex gap-2">
                                    <flux:input class="flex-1"
                                        wire:model.defer="common_mistakes.{{ $i }}" />
                                    <flux:button type="button" variant="ghost" size="sm"
                                        wire:click="removeMistake({{ $i }})">{{ __('exercise.remove') }}
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>
                        <flux:button type="button" size="sm" variant="ghost" wire:click="addMistake">
                            {{ __('exercise.add_mistake') }}</flux:button>
                    </div>
                </div>

                {{-- Otras notas --}}
                <flux:textarea wire:model.defer="breathing" label="{{ __('exercise.breathing') }}" />
                <flux:textarea wire:model.defer="safety_notes" label="{{ __('exercise.safety_notes') }}" />

                {{-- Pivots: Músculos --}}
                <div class="space-y-3">
                    <flux:heading size="md">{{ __('exercise.muscles') }}</flux:heading>
                    <div class="space-y-2">
                        @foreach ($muscleRows as $i => $row)
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-4">
                                    <flux:label class="text-xs">{{ __('exercise.muscle') }}</flux:label>
                                    <flux:select wire:model.defer="muscleRows.{{ $i }}.muscle_id">
                                        <option value=""></option>
                                        @foreach ($muscles as $m)
                                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                                        @endforeach
                                    </flux:select>
                                </div>
                                <div class="md:col-span-4">
                                    <flux:label class="text-xs">{{ __('exercise.role') }}</flux:label>
                                    <flux:select wire:model.defer="muscleRows.{{ $i }}.role">
                                        <option value="primary">{{ __('exercise.role_primary') }}</option>
                                        <option value="secondary">{{ __('exercise.role_secondary') }}</option>
                                        <option value="stabilizer">{{ __('exercise.role_stabilizer') }}</option>
                                    </flux:select>
                                </div>
                                <div class="md:col-span-3">
                                    <flux:input type="number" min="0" max="100"
                                        wire:model.defer="muscleRows.{{ $i }}.involvement_pct"
                                        label="{{ __('exercise.involvement_pct') }}" />
                                </div>
                                <div class="md:col-span-1 flex md:justify-end">
                                    <flux:button type="button" variant="ghost" size="sm"
                                        wire:click="removeMuscleRow({{ $i }})">{{ __('exercise.remove') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <flux:button type="button" size="sm" variant="ghost" wire:click="addMuscleRow">
                        {{ __('exercise.add_muscle') }}</flux:button>
                </div>

                {{-- Pivots: Equipamiento --}}
                <div class="space-y-3">
                    <flux:heading size="md">{{ __('exercise.equipment') }}</flux:heading>
                    <div class="space-y-2">
                        @foreach ($equipmentRows as $i => $row)
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-6">
                                    <flux:label class="text-xs">{{ __('exercise.equipment') }}</flux:label>
                                    <flux:select wire:model.defer="equipmentRows.{{ $i }}.equipment_id">
                                        <option value=""></option>
                                        @foreach ($equip as $e)
                                            <option value="{{ $e->id }}">{{ $e->name }}</option>
                                        @endforeach
                                    </flux:select>
                                </div>
                                <div class="md:col-span-5 flex items-center gap-3">
                                    <flux:checkbox wire:model.defer="equipmentRows.{{ $i }}.is_required" />
                                    <flux:label>{{ __('exercise.is_required') }}</flux:label>
                                </div>
                                <div class="md:col-span-1 flex md:justify-end">
                                    <flux:button type="button" variant="ghost" size="sm"
                                        wire:click="removeEquipmentRow({{ $i }})">
                                        {{ __('exercise.remove') }}</flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <flux:button type="button" size="sm" variant="ghost" wire:click="addEquipmentRow">
                        {{ __('exercise.add_equipment') }}</flux:button>
                </div>

                {{-- Media: imágenes (hasta 10) --}}
                <div class="space-y-3">
                    <flux:heading size="md">{{ __('exercise.images') }}</flux:heading>
                    <flux:input type="file" multiple wire:model="newImages" accept="image/*" />
                    @error('newImages.*')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror

                    {{-- Previews de uploads nuevos --}}
                    @if ($newImages)
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                            @foreach ($newImages as $i => $img)
                                <div class="rounded overflow-hidden border border-gray-200 dark:border-neutral-700">
                                    <img class="w-full h-28 object-cover" src="{{ $img->temporaryUrl() }}"
                                        alt="preview">
                                    <div class="p-2 text-right">
                                        <flux:button size="xs" variant="ghost"
                                            wire:click="removeNewImage({{ $i }})">
                                            {{ __('exercise.remove') }}
                                        </flux:button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif


                    {{-- Imágenes existentes --}}
                    @if ($editMode && $exercise)
                        @php $media = $exercise->getMedia('images'); @endphp
                        @if ($media->count())
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                @foreach ($media as $m)
                                    <div
                                        class="rounded overflow-hidden border border-gray-200 dark:border-neutral-700">
                                        <img class="w-full h-28 object-cover" src="{{ $m->getUrl('thumb') }}"
                                            alt="">
                                        <div class="p-2 text-right">
                                            <flux:button size="xs" variant="ghost"
                                                wire:click="removeMedia({{ $m->id }})">
                                                {{ __('exercise.remove') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                    <flux:text class="text-xs text-gray-500">
                        {{ __('exercise.images_hint', ['n' => 10]) }}
                    </flux:text>
                </div>

                {{-- Acciones --}}
                <div class="flex justify-end gap-4 pt-6 items-center">
                    <x-tenant.action-message on="updated">{{ __('exercise.saved') }}</x-tenant.action-message>
                    <flux:checkbox label="{{ __('exercise.back_list') }}" wire:model.live="back" />
                    <flux:button as="a" variant="ghost"
                        href="{{ route('tenant.dashboard.exercise.exercises.index') }}">
                        {{ $editMode ? __('exercise.back') : __('exercise.cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editMode ? __('exercise.update_exercise') : __('exercise.create_exercise') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
