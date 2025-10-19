<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-6">

            {{-- Header sticky --}}
            <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
                <div class="flex items-center justify-between gap-4 max-w-3xl">
                    <div>
                        <flux:heading size="xl" level="1">
                            {{ $editMode ? __('students.edit_title') : __('students.new_title') }}
                        </flux:heading>
                        <flux:subheading size="lg" class="mb-6">
                            {{ $editMode ? __('students.edit_subheading') : __('students.new_subheading') }}
                        </flux:subheading>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-tenant.action-message on="saved">{{ __('site.saved') }}</x-tenant.action-message>
                        <flux:checkbox size="sm" label="{{ __('site.back_list') }}" wire:model.live="back" />
                        <flux:button as="a" variant="ghost"
                            href="{{ route('tenant.dashboard.students.index') }}" size="sm">
                            {{ __('site.back') }}
                        </flux:button>
                        <flux:button type="submit" size="sm">
                            {{ $editMode ? __('students.update_button') : __('students.create_button') }}
                        </flux:button>
                    </div>
                </div>
                <flux:separator variant="subtle" class="mt-2" />
            </div>

            {{-- Contenido --}}
            <div class="max-w-3xl space-y-4 pt-2">

                {{-- Nombres --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:input wire:model.defer="first_name" label="{{ __('students.first_name') }}" required
                            autocomplete="off" />
                    </div>
                    <div>
                        <flux:input wire:model.defer="last_name" label="{{ __('students.last_name') }}" required
                            autocomplete="off" />
                    </div>
                </div>

                <div>
                    <flux:label>{{ __('students.avatar') }}</flux:label>

                    <div class="flex items-center gap-4">
                        {{-- Contenedor clickable del avatar --}}
                        <div class="relative group cursor-pointer h-20 w-20 rounded-full overflow-hidden border border-gray-300 dark:border-neutral-700 bg-gray-100 dark:bg-neutral-800 flex items-center justify-center"
                            onclick="document.getElementById('avatarInput').click()">
                            {{-- Imagen o iniciales --}}
                            @if ($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" class="h-full w-full object-cover"
                                    alt="avatar">
                            @elseif ($currentAvatarUrl)
                                <img src="{{ $currentAvatarUrl }}" class="h-full w-full object-cover" alt="avatar">
                            @else
                                <span class="text-sm font-medium text-gray-500 dark:text-neutral-400">
                                    {{ strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1)) }}
                                </span>
                            @endif

                            {{-- Estado de carga mientras sube el archivo --}}
                            <div wire:loading.flex wire:target="avatar"
                                class="absolute inset-0 bg-black/60 text-white flex flex-col items-center justify-center text-xs font-medium z-10">
                                <x-icons.lucide.loader class="animate-spin h-5 w-5" />

                            </div>

                            {{-- Overlay al pasar el mouse --}}
                            <div
                                class="absolute inset-0 bg-black/40 text-white opacity-0 group-hover:opacity-100 transition flex flex-col items-center justify-center text-xs font-medium z-0">
                                <x-icons.lucide.upload class="h-5 w-5" />

                            </div>
                        </div>

                        {{-- Acciones laterales --}}
                        <div class="flex flex-col gap-2">
                            {{-- Input oculto --}}
                            <input id="avatarInput" type="file" wire:model="avatar" accept="image/*"
                                class="hidden" />

                            {{-- Botón alternativo (por accesibilidad) --}}
                            <flux:button size="sm" variant="outline"
                                onclick="document.getElementById('avatarInput').click()">
                                {{ $avatar || $currentAvatarUrl ? __('students.change_avatar') : __('students.upload_avatar') }}
                            </flux:button>

                            {{-- Botón eliminar --}}
                            @if ($avatar || $currentAvatarUrl)
                                <flux:button size="sm" variant="ghost" wire:click="deleteAvatar">
                                    {{ __('students.remove_avatar') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>




                {{-- Contacto básico --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:input wire:model.defer="email" label="{{ __('students.email') }}" type="email" required
                            autocomplete="off" />
                    </div>
                    <div>
                        <flux:input wire:model.defer="phone" label="{{ __('students.phone') }}" autocomplete="off" />
                    </div>
                </div>

                {{-- Estado y plan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:select wire:model.defer="status" label="{{ __('students.status') }}">
                            <option value="active">{{ __('students.status.active') }}</option>
                            <option value="paused">{{ __('students.status.paused') }}</option>
                            <option value="inactive">{{ __('students.status.inactive') }}</option>
                            <option value="prospect">{{ __('students.status.prospect') }}</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:select wire:model.defer="commercial_plan_id" label="{{ __('students.plan') }}">
                            <option value="">{{ __('common.none') }}</option>
                            @foreach ($plans as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                {{-- Nivel y facturación --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:input wire:model.defer="current_level" label="{{ __('students.level') }}"
                            autocomplete="off" />
                    </div>
                    <div>
                        <flux:select wire:model.defer="billing_frequency"
                            label="{{ __('students.billing_frequency') }}">
                            <option value="monthly">{{ __('students.monthly') }}</option>
                            <option value="yearly">{{ __('students.yearly') }}</option>
                        </flux:select>
                    </div>
                </div>

                {{-- Estado de cuenta / usuario --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:select wire:model.defer="account_status" label="{{ __('students.account_status') }}">
                            <option value="ok">{{ __('students.account_status_ok') }}</option>
                            <option value="pending">{{ __('students.account_status_pending') }}</option>
                            <option value="debt">{{ __('students.account_status_debt') }}</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:select wire:model.defer="is_user_enabled" label="{{ __('students.user_enabled') }}">
                            <option value="1">{{ __('common.yes') }}</option>
                            <option value="0">{{ __('common.no') }}</option>
                        </flux:select>
                    </div>
                </div>

                {{-- Objetivo --}}
                <flux:textarea wire:model.defer="goal" label="{{ __('students.goal') }}" rows="3"
                    placeholder="{{ __('students.goal_placeholder') }}" />

                {{-- ----------------- Secciones JSON ----------------- --}}

                {{-- Personal Data --}}
                <flux:separator variant="subtle" class="mt-8" />
                <flux:heading size="lg">{{ __('students.personal_data_section') }}</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:input type="date" wire:model.defer="personal.birth_date"
                            label="{{ __('students.birth_date') }}" />
                    </div>
                    <div>
                        <flux:select wire:model.defer="personal.gender" label="{{ __('students.gender') }}">
                            <option value="">{{ __('common.select') }}</option>
                            <option value="male">{{ __('students.gender_male') }}</option>
                            <option value="female">{{ __('students.gender_female') }}</option>
                            <option value="other">{{ __('students.gender_other') }}</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:input type="number" step="1" wire:model.defer="personal.height_cm"
                            label="{{ __('students.height_cm') }}" />
                    </div>
                    <div>
                        <flux:input type="number" step="0.1" wire:model.defer="personal.weight_kg"
                            label="{{ __('students.weight_kg') }}" />
                    </div>
                </div>

                {{-- Health Data --}}
                <flux:separator variant="subtle" class="mt-8" />
                <flux:heading size="lg">{{ __('students.health_data_section') }}</flux:heading>
                <div class="space-y-4">
                    <flux:textarea wire:model.defer="health.injuries" label="{{ __('students.injuries') }}"
                        rows="2" />
                </div>

                {{-- Training Data --}}
                <flux:separator variant="subtle" class="mt-8" />
                <flux:heading size="lg">{{ __('students.training_data_section') }}</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:select wire:model.defer="training.experience" label="{{ __('students.experience') }}">
                            <option value="beginner">{{ __('students.experience_beginner') }}</option>
                            <option value="intermediate">{{ __('students.experience_intermediate') }}</option>
                            <option value="advanced">{{ __('students.experience_advanced') }}</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:input type="number" wire:model.defer="training.days_per_week"
                            label="{{ __('students.days_per_week') }}" />
                    </div>
                </div>

                {{-- Communication Data --}}
                <flux:separator variant="subtle" class="mt-8" />
                <flux:heading size="lg">{{ __('students.communication_section') }}</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:select wire:model.defer="communication.language" label="{{ __('students.language') }}">
                            <option value="es">Español</option>
                            <option value="en">English</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:label>{{ __('students.notifications') }}</flux:label>
                        <div class="flex flex-col gap-2 mt-2">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                                <input type="checkbox" wire:model.defer="communication.notifications.new_plan"
                                    class="form-checkbox accent-blue-600 dark:accent-blue-400 rounded focus:ring-2 focus:ring-blue-500" />
                                {{ __('students.notification_new_plan') }}
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                                <input type="checkbox" wire:model.defer="communication.notifications.session_reminder"
                                    class="form-checkbox accent-blue-600 dark:accent-blue-400 rounded focus:ring-2 focus:ring-blue-500" />
                                {{ __('students.notification_session_reminder') }}
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Extra Data --}}
                <flux:separator variant="subtle" class="mt-8" />
                <flux:heading size="lg">{{ __('students.extra_data_section') }}</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:input wire:model.defer="extra.emergency_contact.name"
                            label="{{ __('students.emergency_contact_name') }}" />
                    </div>
                    <div>
                        <flux:input wire:model.defer="extra.emergency_contact.phone"
                            label="{{ __('students.emergency_contact_phone') }}" />
                    </div>
                </div>

                {{-- Footer --}}
                <div class="pt-6 max-w-3xl">
                    <div class="flex justify-end gap-3 items-center text-sm opacity-80">
                        <x-tenant.action-message on="saved">{{ __('site.saved') }}</x-tenant.action-message>
                        <flux:checkbox size="sm" label="{{ __('site.back_list') }}" wire:model.live="back" />
                        <flux:button as="a" variant="ghost"
                            href="{{ route('tenant.dashboard.students.index') }}" size="sm">
                            {{ __('site.back') }}
                        </flux:button>
                        <flux:button type="submit" size="sm">
                            {{ $editMode ? __('students.update_button') : __('students.create_button') }}
                        </flux:button>
                    </div>
                </div>

                <flux:separator variant="subtle" class="mt-8" />
            </div>
        </form>
    </div>
</div>
