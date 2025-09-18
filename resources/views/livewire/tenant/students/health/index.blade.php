<div class="space-y-6">
    <div>
        <flux:heading size="xl" level="1">{{ __('site.health_title') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">
            {{ __('site.health_subheading') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <form wire:submit.prevent="save" class="max-w-6xl space-y-6">

        {{-- Apto físico --}}
        <section class="space-y-6">
            <flux:heading size="md">{{ __('site.section_identity_contact') }}</flux:heading>


            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Estado APTO --}}
                <div>
                    <flux:label class="block mb-2">{{ __('site.apt_fitness_status') }}</flux:label>
                    <flux:select wire:model.defer="apt_fitness_status">
                        <option value="">{{ __('site.select_option') }}</option>
                        <option value="valid">{{ __('site.valid') }}</option>
                        <option value="expired">{{ __('site.expired') }}</option>
                        <option value="not_required">{{ __('site.not_required') }}</option>
                    </flux:select>
                </div>

                {{-- Vence el --}}
                <div>
                    <flux:label class="block mb-2">{{ __('site.apt_expires_at') }}</flux:label>
                    <flux:input wire:model.defer="apt_fitness_expires_at" type="date" />
                </div>

                {{-- Archivo APTO --}}
                <div>
                    <flux:label class="block mb-2">{{ __('site.apt_upload') }}</flux:label>

                    @php $existingApto = $student->getFirstMedia('apto'); @endphp

                    {{-- Preview --}}
                    @if ($aptFile)
                        @php
                            $ext = strtolower(pathinfo($aptFile->getClientOriginalName(), PATHINFO_EXTENSION));
                            $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif']);
                        @endphp
                        @if ($isImg && method_exists($aptFile, 'temporaryUrl'))
                            <img src="{{ $aptFile->temporaryUrl() }}" class="w-40 h-28 object-cover border rounded"
                                alt="apto">
                        @else
                            <div class="text-sm text-gray-500 truncate max-w-xs">
                                {{ $aptFile->getClientOriginalName() }} <span class="text-gray-400">(no preview)</span>
                            </div>
                        @endif
                    @elseif ($existingApto)
                        <div class="text-sm text-gray-500 truncate max-w-xs">
                            {{ $existingApto->file_name }}
                        </div>
                    @endif

                    <div class="mt-2 flex gap-2">
                        <input type="file" wire:model="aptFile"
                            wire:key="aptFile-{{ $student->id }}-{{ $aptFile ? 'has' : 'none' }}"
                            class="block w-full text-sm" accept=".jpg,.jpeg,.png,.webp,.gif,.avif,.pdf">
                        @if ($aptFile)
                            <flux:button size="xs" variant="ghost" wire:click="removeTempApto">✕</flux:button>
                        @elseif ($existingApto)
                            <flux:button size="xs" variant="ghost" wire:click="removeApto">{{ __('site.delete') }}
                            </flux:button>
                        @endif
                    </div>

                    @error('aptFile')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>


        </section>

        <flux:separator variant="subtle" />

        {{-- PAR-Q --}}
        <section class="space-y-6">
            <flux:heading size="md">{{ __('site.section_parq') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <flux:label class="block mb-2">{{ __('site.parq_result') }}</flux:label>
                    <flux:select wire:model.defer="parq_result">
                        <option value="">{{ __('site.select_option') }}</option>
                        <option value="fit">{{ __('site.parq_fit') }}</option>
                        <option value="refer_to_md">{{ __('site.parq_refer') }}</option>
                    </flux:select>
                </div>

                <div>
                    <flux:label class="block mb-2">{{ __('site.parq_date') }}</flux:label>
                    <flux:input wire:model.defer="parq_date" type="date" />
                </div>
            </div>


        </section>

        <flux:separator variant="subtle" />

        {{-- Salud & antecedentes --}}
        <section class="space-y-6">
            <flux:heading size="md">{{ __('site.section_medical_background') }}</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Lesiones --}}
                <div>
                    <flux:label class="text-xs block mb-2">{{ __('site.injuries') }}</flux:label>
                    <div class="flex gap-2">
                        <flux:input wire:model.defer="injuryInput" placeholder="{{ __('site.add_injury') }}" />
                        <flux:button type="button" wire:click="addInjury">{{ __('site.add_item') }}</flux:button>
                    </div>
                    <ul class="list-disc pl-5 space-y-1 mt-2">
                        @forelse($injuries as $i => $injury)
                            <li class="flex items-center justify-between">
                                <span>{{ $injury }}</span>
                                <button type="button" wire:click="removeInjury({{ $i }})"
                                    class="text-xs text-red-500">
                                    {{ __('site.remove_item') }}
                                </button>
                            </li>
                        @empty
                            <li class="text-gray-500 text-sm">{{ __('site.no_items_yet') }}</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Antecedentes médicos --}}
                <div>
                    <flux:label class="text-xs block mb-2">{{ __('site.medical_history') }}</flux:label>
                    <div class="flex gap-2">
                        <flux:input wire:model.defer="medicalHistoryInput"
                            placeholder="{{ __('site.add_medical_history') }}" />
                        <flux:button type="button" wire:click="addMedicalHistory">{{ __('site.add_item') }}
                        </flux:button>
                    </div>
                    <ul class="list-disc pl-5 space-y-1 mt-2">
                        @forelse($medical_history as $i => $mh)
                            <li class="flex items-center justify-between">
                                <span>{{ $mh }}</span>
                                <button type="button" wire:click="removeMedicalHistory({{ $i }})"
                                    class="text-xs text-red-500">
                                    {{ __('site.remove_item') }}
                                </button>
                            </li>
                        @empty
                            <li class="text-gray-500 text-sm">{{ __('site.no_items_yet') }}</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Medicación / alergias --}}
                <div>
                    <flux:label class="text-xs block mb-2">{{ __('site.medications_allergies') }}</flux:label>
                    <div class="flex gap-2">
                        <flux:input wire:model.defer="medAllergyInput"
                            placeholder="{{ __('site.add_med_allergy') }}" />
                        <flux:button type="button" wire:click="addMedAllergy">{{ __('site.add_item') }}</flux:button>
                    </div>
                    <ul class="list-disc pl-5 space-y-1 mt-2">
                        @forelse($medications_allergies as $i => $ma)
                            <li class="flex items-center justify-between">
                                <span>{{ $ma }}</span>
                                <button type="button" wire:click="removeMedAllergy({{ $i }})"
                                    class="text-xs text-red-500">
                                    {{ __('site.remove_item') }}
                                </button>
                            </li>
                        @empty
                            <li class="text-gray-500 text-sm">{{ __('site.no_items_yet') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </section>

        <flux:separator variant="subtle" />

        {{-- Contacto de emergencia --}}
        <section class="space-y-6">
            <flux:heading size="md">{{ __('site.section_emergency_contact') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <flux:input wire:model.defer="emergency_contact.name" label="{{ __('site.emergency_name') }}" />
                </div>
                <div>
                    <flux:input wire:model.defer="emergency_contact.relation"
                        label="{{ __('site.emergency_relation') }}" />
                </div>
                <div>
                    <flux:input wire:model.defer="emergency_contact.phone"
                        label="{{ __('site.emergency_phone') }}" />
                </div>
            </div>

        </section>

        <flux:separator variant="subtle" />

        {{-- Consentimientos --}}
        <section class="space-y-6">
            <flux:heading size="md">{{ __('site.section_consents') }}</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <flux:input wire:model.defer="tos_accepted_at" type="datetime-local"
                        label="{{ __('site.tos_accepted_at') }}" />
                </div>

                <div>
                    <flux:input wire:model.defer="sensitive_data_consent_at" type="datetime-local"
                        label="{{ __('site.sensitive_data_consent_at') }}" />
                </div>

                <div>
                    <flux:label class="text-xs">{{ __('site.image_consent') }}</flux:label>
                    <flux:checkbox wire:model.defer="image_consent" />
                </div>

                <div>
                    <flux:input wire:model.defer="image_consent_at" type="datetime-local"
                        label="{{ __('site.image_consent_at') }}" />

                </div>
            </div>

        </section>

        {{-- Acciones --}}
        <div class="flex justify-end gap-4 pt-6 items-center">
            <x-tenant.action-message on="updated">{{ __('site.saved') }}</x-tenant.action-message>
            <flux:button type="submit" variant="primary">{{ __('site.update_student') }}</flux:button>
        </div>
    </form>
</div>
