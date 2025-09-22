<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-10">
            <div>
                <flux:heading size="xl" level="1">
                    {{ $editMode ? __('site.edit_student') : __('site.new_student') }}
                </flux:heading>
                <flux:subheading size="lg" class="mb-6">
                    {{ __('site.student_subheading') }}
                </flux:subheading>
                <flux:separator variant="subtle" />
            </div>

            <div class="max-w-6xl space-y-10">
                {{-- Identificación y contacto --}}
                <section class="space-y-6">
                    <flux:heading size="md">{{ __('site.section_identity_contact') }}</flux:heading>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <flux:input wire:model.defer="first_name" label="{{ __('site.first_name') }}"
                                autocomplete="off" />
                        </div>
                        <div>
                            <flux:input wire:model.defer="last_name" label="{{ __('site.last_name') }}"
                                autocomplete="off" />
                        </div>
                        <div>
                            <flux:input wire:model.defer="document_number" label="{{ __('site.document') }}"
                                autocomplete="off" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <flux:input wire:model.defer="email" label="{{ __('site.email') }}" type="email"
                                autocomplete="off" />
                        </div>
                        <div>
                            <flux:input wire:model.defer="phone" label="{{ __('site.phone') }}" autocomplete="off" />
                        </div>
                        <div>
                            <flux:label class="text-xs">{{ __('site.status') }}</flux:label>
                            <flux:select wire:model.defer="status" size="md">
                                <option value="">{{ __('site.select_option') }}</option>
                                <option value="active">{{ __('site.active') }}</option>
                                <option value="paused">{{ __('site.paused') }}</option>
                                <option value="inactive">{{ __('site.inactive') }}</option>
                                <option value="prospect">{{ __('site.prospect') }}</option>
                            </flux:select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <flux:label class="text-xs">{{ __('site.timezone') }}</flux:label>
                            <flux:select wire:model.defer="timezone">
                                <option value="">{{ __('site.select_option') }}</option>
                                @foreach ($timezones as $tz)
                                    <option value="{{ $tz['id'] }}">{{ $tz['label'] }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                        <div>
                            <flux:input wire:model.defer="birth_date" type="date"
                                label="{{ __('site.birth_date') }}" />
                        </div>
                        <div>
                            <flux:label class="text-xs">{{ __('site.gender') }}</flux:label>
                            <flux:select wire:model.defer="gender">
                                <option value="">{{ __('site.select_option') }}</option>
                                <option value="male">{{ __('site.gender_male') }}</option>
                                <option value="female">{{ __('site.gender_female') }}</option>
                                <option value="non_binary">{{ __('site.gender_non_binary') }}</option>
                                <option value="other">{{ __('site.other') }}</option>
                            </flux:select>
                        </div>
                    </div>

                </section>

                <flux:separator variant="subtle" />

                <section class="space-y-6">
                    <flux:heading size="md">{{ __('site.section_profile_access') }}</flux:heading>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start content-start">
                        {{-- Col 1: Avatar --}}
                        <div class="space-y-2 self-start">
                            <flux:label class="text-xs">{{ __('site.avatar') }}</flux:label>

                            {{-- Contenedor fijo para evitar saltos al previsualizar --}}
                            <div
                                class="relative w-20 h-20 rounded-full overflow-hidden border bg-gray-50 dark:bg-neutral-800">
                                @if ($avatar && method_exists($avatar, 'temporaryUrl'))
                                    <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover"
                                        alt="avatar">
                                @elseif ($editMode)
                                    @php
                                        $existingUrl = optional(
                                            \App\Models\Tenant\Student::find($id),
                                        )->getFirstMediaUrl('avatar');
                                    @endphp
                                    @if ($existingUrl)
                                        <img src="{{ $existingUrl }}" class="w-full h-full object-cover"
                                            alt="avatar">
                                    @else
                                        {{-- Placeholder consistente --}}
                                        <div class="w-full h-full grid place-items-center text-xs text-gray-400">—</div>
                                    @endif
                                @else
                                    <div class="w-full h-full grid place-items-center text-xs text-gray-400">—</div>
                                @endif
                            </div>

                            <div class="flex gap-2">
                                <input type="file" wire:model="avatar" accept="image/*" class="block w-full text-sm">
                                @if ($avatar)
                                    <flux:button size="xs" variant="ghost" wire:click="removeTempAvatar">✕
                                    </flux:button>
                                @elseif ($editMode && !empty($existingUrl))
                                    <flux:button size="xs" variant="ghost" wire:click="removeAvatar">
                                        {{ __('site.delete') }}</flux:button>
                                @endif
                            </div>
                            @error('avatar')
                                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Col 2: Idioma --}}
                        <div class="self-start">
                            <flux:label class="text-xs">{{ __('site.language') }}</flux:label>
                            <flux:select wire:model.defer="language">
                                <option value="">{{ __('site.select_option') }}</option>
                                <option value="es">Español</option>
                                <option value="en">English</option>
                            </flux:select>
                        </div>

                        {{-- Col 3: Habilitar usuario (label superior + label del check) --}}
                        <div class="self-start">
                            <flux:label class="text-xs block mb-2">{{ __('site.enable_user_access') }}</flux:label>
                            <div class="flex items-start gap-2">
                                <flux:checkbox wire:model.defer="is_user_enabled" />
                                <span class="text-sm leading-5">{{ __('site.yes') }}</span>
                            </div>
                        </div>
                    </div>
                </section>



                <flux:separator variant="subtle" />

                {{-- Objetivos y preferencias --}}
                <section class="space-y-6">
                    <flux:heading size="md">{{ __('site.section_goals_preferences') }}</flux:heading>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <flux:label class="text-xs">{{ __('site.primary_goal') }}</flux:label>
                            <flux:select wire:model.defer="primary_training_goal_id">
                                <option value="">{{ __('site.select_option') }}</option>
                                @foreach ($goals as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                        <div>
                            <flux:input wire:model.defer="height_cm" type="number" step="0.1"
                                label="{{ __('site.height_cm') }}" />
                        </div>
                        <div>
                            <flux:input wire:model.defer="weight_kg" type="number" step="0.1"
                                label="{{ __('site.weight_kg') }}" />
                        </div>
                    </div>
                    <div>
                        <flux:textarea wire:model.defer="availability_text" label="{{ __('site.availability') }}" />
                    </div>
                    <div>
                        <flux:textarea wire:model.defer="experience_summary"
                            label="{{ __('site.experience_summary') }}" />
                    </div>
                </section>

                <flux:separator variant="subtle" />

                {{-- Salud y antecedentes (extracto apto) --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <flux:label class="text-xs">{{ __('site.apt_fitness_status') }}</flux:label>
                        <flux:select wire:model.defer="apt_fitness_status">
                            <option value="">{{ __('site.select_option') }}</option>
                            <option value="valid">{{ __('site.valid') }}</option>
                            <option value="expired">{{ __('site.expired') }}</option>
                            <option value="not_required">{{ __('site.not_required') }}</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:input wire:model.defer="apt_fitness_expires_at" type="date"
                            label="{{ __('site.apt_expires_at') }}" />
                    </div>

                    <div class="space-y-2 self-start">
                        <flux:label class="text-xs">{{ __('site.apt_upload') }}</flux:label>

                        {{-- Si hay archivo temporal, solo previsualizar si es imagen --}}
                        @if ($aptFile)
                            @php
                                $ext = strtolower(pathinfo($aptFile->getClientOriginalName(), PATHINFO_EXTENSION));
                                $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif']);
                            @endphp

                            @if ($isImg && method_exists($aptFile, 'temporaryUrl'))
                                <img src="{{ $aptFile->temporaryUrl() }}"
                                    class="w-40 h-28 object-cover border rounded" alt="apto">
                            @else
                                <div class="text-xs text-gray-500 truncate max-w-xs">
                                    {{ $aptFile->getClientOriginalName() }} <span class="text-gray-400">(no
                                        preview)</span>
                                </div>
                            @endif
                        @elseif ($editMode)
                            @php
                                $student = \App\Models\Tenant\Student::find($id);
                                $media = $student?->getFirstMedia('apto');
                            @endphp
                            @if ($media)
                                <div class="text-xs text-gray-500 truncate max-w-xs">
                                    {{ $media->file_name }}
                                </div>
                            @endif
                        @endif

                        <div class="flex gap-2">
                            <input type="file" wire:model="aptFile" class="block w-full text-sm">
                            @if ($aptFile)
                                <flux:button size="xs" variant="ghost" wire:click="removeTempApto">✕
                                </flux:button>
                            @elseif (!empty($media))
                                <flux:button size="xs" variant="ghost" wire:click="removeApto">
                                    {{ __('site.delete') }}</flux:button>
                            @endif
                        </div>

                        @error('aptFile')
                            <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <flux:separator variant="subtle" />

                {{-- Planificación / Fase actual --}}
                <section class="space-y-6">
                    <flux:heading size="md">{{ __('site.section_planning') }}</flux:heading>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <flux:label class="text-xs">{{ __('site.training_phase') }}</flux:label>
                            <flux:select wire:model.defer="current_training_phase_id">
                                <option value="">{{ __('site.select_option') }}</option>
                                @foreach ($phases as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                        <flux:input wire:model.defer="plan_start_date" type="date"
                            label="{{ __('site.plan_start') }}" />
                        <flux:input wire:model.defer="plan_end_date" type="date"
                            label="{{ __('site.plan_end') }}" />
                    </div>
                </section>

                <flux:separator variant="subtle" />

                {{-- Comunicación y administración --}}
                <section class="space-y-6">
                    <flux:heading size="md">{{ __('site.section_comm_admin') }}</flux:heading>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <flux:label class="text-xs">{{ __('site.preferred_channel') }}</flux:label>
                            <flux:select wire:model.defer="preferred_channel_id">
                                <option value="">{{ __('site.select_option') }}</option>
                                @foreach ($channels as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div>
                            <flux:label class="text-xs">{{ __('site.commercial_plan') }}</flux:label>
                            <flux:select wire:model.defer="commercial_plan_id">
                                <option value="">{{ __('site.select_option') }}</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div>
                            <flux:label class="text-xs">{{ __('site.billing_frequency') }}</flux:label>
                            <flux:select wire:model.defer="billing_frequency">
                                <option value="">{{ __('site.select_option') }}</option>
                                <option value="monthly">{{ __('site.monthly') }}</option>
                                <option value="quarterly">{{ __('site.quarterly') }}</option>
                                <option value="yearly">{{ __('site.yearly') }}</option>
                            </flux:select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <flux:label class="text-xs">{{ __('site.payment_method') }}</flux:label>
                            <flux:select wire:model.defer="preferred_payment_method_id">
                                <option value="">{{ __('site.select_option') }}</option>
                                @foreach ($methods as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div>
                            <flux:label class="text-xs">{{ __('site.account_status') }}</flux:label>
                            <flux:select wire:model.defer="account_status">
                                <option value="">{{ __('site.select_option') }}</option>
                                <option value="on_time">{{ __('site.account_on_time') }}</option>
                                <option value="due">{{ __('site.account_due') }}</option>
                                <option value="review">{{ __('site.account_review') }}</option>
                            </flux:select>
                        </div>

                        <flux:input wire:model.defer="lead_source" label="{{ __('site.lead_source') }}" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <flux:label class="text-xs">{{ __('site.notifications') }}</flux:label>
                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-2 text-xs">
                                    <flux:checkbox wire:model.defer="notifications.marketing" />
                                    {{ __('site.marketing') }}
                                </label>
                                <label class="inline-flex items-center gap-2 text-xs">
                                    <flux:checkbox wire:model.defer="notifications.reminders" />
                                    {{ __('site.reminders') }}
                                </label>
                                <label class="inline-flex items-center gap-2 text-xs">
                                    <flux:checkbox wire:model.defer="notifications.news" /> {{ __('site.news') }}
                                </label>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <flux:textarea wire:model.defer="private_notes" label="{{ __('site.private_notes') }}" />
                        </div>
                    </div>
                </section>

                <flux:separator variant="subtle" />

                {{-- Consentimientos y relaciones --}}
                <section class="space-y-6">
                    <flux:heading size="md">{{ __('site.section_consents_relations') }}</flux:heading>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <flux:input wire:model.defer="tos_accepted_at" type="datetime-local"
                                label="{{ __('site.tos_accepted_at') }}" />
                        </div>
                        <div>
                            <flux:input wire:model.defer="sensitive_data_consent_at" type="datetime-local"
                                label="{{ __('site.sensitive_data_consent_at') }}" />
                        </div>

                        <div class="flex items-center gap-3">
                            <flux:checkbox wire:model.defer="image_consent" />
                            <flux:label>{{ __('site.image_consent') }}</flux:label>
                        </div>
                        <div>
                            <flux:input wire:model.defer="image_consent_at" type="datetime-local"
                                label="{{ __('site.image_consent_at') }}" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <flux:input wire:model.defer="emergency_contact.name"
                            label="{{ __('site.emergency_name') }}" />
                        <flux:input wire:model.defer="emergency_contact.relation"
                            label="{{ __('site.emergency_relation') }}" />
                        <flux:input wire:model.defer="emergency_contact.phone"
                            label="{{ __('site.emergency_phone') }}" />
                    </div>


                </section>


                <flux:separator variant="subtle" />

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

            </div>
            {{-- Acciones --}}
            <div class="flex justify-end gap-4 pt-6 items-center">
                <x-tenant.action-message on="updated">
                    {{ __('site.saved') }}
                </x-tenant.action-message>

                <flux:checkbox label="{{ __('site.back_list') }}" wire:model.live="back" />

                <flux:button as="a" variant="ghost" href="{{ route('tenant.dashboard.students.index') }}">
                    {{ $editMode ? __('site.back') : __('site.cancel') }}
                </flux:button>

                <flux:button type="submit" variant="primary">
                    {{ $editMode ? __('site.update_student') : __('site.create_student') }}
                </flux:button>
            </div>
        </form>
    </div>

</div>
