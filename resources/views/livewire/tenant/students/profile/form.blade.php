<div class="space-y-6">
    {{-- --}}
    <div>
        <flux:heading size="xl" level="1">{{ __('Datos Personales') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">
            {{ __('site.student_subheading') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <form wire:submit.prevent="save" class="max-w-6xl space-y-6">
        {{-- Identidad y contacto --}}
        <section class="space-y-6">
            <flux:heading size="md">{{ __('site.section_identity_contact') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <flux:input wire:model.defer="first_name" label="{{ __('site.first_name') }}" />
                </div>
                <div>
                    <flux:input wire:model.defer="last_name" label="{{ __('site.last_name') }}" />
                </div>
                <div>
                    <flux:input wire:model.defer="document_number" label="{{ __('site.document') }}" />
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <flux:input wire:model.defer="email" label="{{ __('site.email') }}" type="email" />
                </div>
                <div>
                    <flux:input wire:model.defer="phone" label="{{ __('site.phone') }}" />
                </div>
                <div>
                    <flux:select label="{{ __('site.status') }}" wire:model.defer="status">
                        <option value="">{{ __('site.select_option') }}</option>
                        <option value="active">{{ __('site.active') }}</option>
                        <option value="paused">{{ __('site.paused') }}</option>
                        <option value="inactive">{{ __('site.inactive') }}</option>
                        <option value="prospect">{{ __('site.prospect') }}</option>
                    </flux:select>
                </div>
            </div>
        </section>

        {{-- Perfil y acceso --}}
        <section class="space-y-6">
            <flux:heading size="md">{{ __('site.section_profile_access') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Avatar --}}
                <div>
                    <flux:label class="text-xs">{{ __('site.avatar') }}</flux:label>
                    <input type="file" wire:model="avatar" accept="image/*" class="block w-full text-sm" />
                </div>
                <div>
                    <flux:select wire:model.defer="language" label="{{ __('site.language') }}">
                        <option value="es">Español</option>
                        <option value="en">English</option>
                    </flux:select>
                </div>
                <div>
                    <flux:label >{{ __('site.enable_user_access') }}</flux:label>
                    <flux:checkbox wire:model.defer="is_user_enabled" />
                </div>
            </div>
        </section>

        {{-- Objetivos y preferencias --}}
        <section class="space-y-6">
            <flux:heading size="md">{{ __('site.section_goals_preferences') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <flux:select label="{{ __('site.primary_goal') }}" wire:model.defer="primary_training_goal_id">
                        <option value="">{{ __('site.select_option') }}</option>
                        @foreach ($goals as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <flux:input wire:model.defer="height_cm" type="number" step="0.1"
                    label="{{ __('site.height_cm') }}" />
                <flux:input wire:model.defer="weight_kg" type="number" step="0.1"
                    label="{{ __('site.weight_kg') }}" />
            </div>
            <flux:textarea wire:model.defer="availability_text" label="{{ __('site.availability') }}" />
            <flux:textarea wire:model.defer="experience_summary" label="{{ __('site.experience_summary') }}" />
        </section>

        {{-- Tags --}}
        <section class="space-y-4">
            <flux:heading size="md">{{ __('site.tags') }}</flux:heading>

            {{-- Input de búsqueda/creación --}}
            <div class="max-w-xl">
                <flux:input wire:model.live.debounce.300ms="tagQuery"
                    placeholder="{{ __('site.type_to_search_or_create') }}" />

                {{-- Sugerencias (si hay query) --}}
                @if ($tagQuery !== '')
                    <div class="mt-1 border rounded-lg bg-white dark:bg-neutral-800 text-sm overflow-hidden">
                        @forelse($tagSuggestions as $sug)
                            <button type="button"
                                class="w-full text-left px-3 py-2 hover:bg-gray-50 dark:hover:bg-neutral-700 flex items-center gap-2"
                                wire:click="selectTag({{ $sug['id'] }})">
                                @if (!empty($sug['color']))
                                    <span class="inline-block w-2.5 h-2.5 rounded-full"
                                        style="background-color: {{ $sug['color'] }}"></span>
                                @endif
                                <span>{{ $sug['name'] }}</span>
                                <span class="ml-auto text-xs text-gray-400">#{{ $sug['code'] }}</span>
                            </button>
                        @empty
                            <div class="px-3 py-2 flex items-center justify-between">
                                <span class="text-gray-500">{{ __('site.no_results') }}</span>
                                <flux:button size="xs" variant="ghost" wire:click="addTagFromQuery">
                                    {{ __('site.create_tag_named') }} “{{ $tagQuery }}”
                                </flux:button>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>

            {{-- Chips seleccionados --}}
            <div class="flex flex-wrap gap-2">
                @forelse($selectedTags as $t)
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs border"
                        style="--c: {{ $t['color'] ?? '#e5e7eb' }}; border-color: var(--c); color: var(--c);">
                        <span class="inline-block w-2 h-2 rounded-full"
                            style="background-color: {{ $t['color'] ?? '#e5e7eb' }}"></span>
                        {{ $t['name'] }}
                        <button type="button" class="ml-1 text-[11px]"
                            wire:click="removeTag({{ $t['id'] }})">✕</button>
                    </span>
                @empty
                    <span class="text-xs text-gray-500">{{ __('site.no_tags_selected') }}</span>
                @endforelse
            </div>
        </section>

        {{-- Acciones --}}
        <div class="flex justify-end gap-4 pt-6 items-center">
            <x-tenant.action-message on="updated">
                {{ __('site.saved') }}
            </x-tenant.action-message>

            <flux:checkbox label="{{ __('site.back_list') }}" wire:model.live="back" />

            <flux:button as="a" variant="ghost" href="{{ route('tenant.dashboard.students.index') }}">
                {{ __('site.back') }}
            </flux:button>

            <flux:button type="submit" variant="primary">
                {{ __('site.update_student') }}
            </flux:button>
        </div>


    </form>
</div>
