<x-simple-form submit="save" :edit-mode="$editMode" :back-route="route('tenant.dashboard.exercises.index')" :back-model="'back'"
    title-new="{{ __('exercises.new_title') }}" title-edit="{{ __('exercises.edit_title') }}"
    sub-new="{{ __('exercises.new_subheading') }}" sub-edit="{{ __('exercises.edit_subheading') }}"
    create-label="{{ __('common.save') }}" update-label="{{ __('common.update') }}" back-label="{{ __('site.back') }}"
    back-list-label="{{ __('site.back_list') }}">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <flux:input wire:model.defer="name" label="{{ __('exercises.name') }}" required autocomplete="off" />
        </div>
        <div>
            <flux:input wire:model.defer="category" label="{{ __('exercises.category') }}" autocomplete="off" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <flux:select wire:model.defer="level" label="{{ __('exercises.level') }}">
                <option value="">{{ __('common.select') }}</option>
                <option value="beginner">{{ __('exercises.levels.beginner') }}</option>
                <option value="intermediate">{{ __('exercises.levels.intermediate') }}</option>
                <option value="advanced">{{ __('exercises.levels.advanced') }}</option>
            </flux:select>
        </div>
        <div>
            <flux:input wire:model.defer="equipment" label="{{ __('exercises.equipment') }}" autocomplete="off" />
        </div>
    </div>

    <div class="space-y-4">
        <div>
            <flux:textarea wire:model.defer="description" label="{{ __('exercises.description') }}" rows="3"
                placeholder="{{ __('exercises.description_placeholder') }}" />
        </div>
        <div>
            <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-800 dark:text-neutral-200">
                <input type="checkbox"
                    class="form-checkbox accent-blue-600 dark:accent-blue-400 rounded focus:ring-2 focus:ring-blue-500"
                    wire:model.defer="is_active" />
                {{ __('exercises.is_active') }}
            </label>
        </div>
    </div>

    <div class="space-y-4">
        <flux:label>{{ __('exercises.images') }}</flux:label>

        {{-- Botón personalizado para subir imágenes --}}
        <div>
            <input id="exerciseImagesInput" type="file" multiple wire:model="newImages" accept="image/*"
                class="hidden" />

            <flux:button size="sm" icon="image-plus"
                onclick="document.getElementById('exerciseImagesInput').click()">
                {{ __('common.add_images') }}
            </flux:button>

            @error('newImages.*')
                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>

        {{-- Galería combinada (guardadas + pendientes) --}}
        <div class="flex flex-wrap gap-3 mt-3">
            {{-- Imágenes ya guardadas --}}
            @if ($exercise && $exercise->exists && $exercise->getMedia('images')->count())
                @foreach ($exercise->getMedia('images') as $media)
                    @php
                        $thumbUrl = $media->hasGeneratedConversion('thumb')
                            ? $media->getUrl('thumb')
                            : $media->getUrl();
                    @endphp
                    <div class="relative group">
                        <img src="{{ $thumbUrl }}"
                            class="h-20 w-20 object-cover rounded-lg border border-gray-200 dark:border-neutral-700" />
                        <button type="button" wire:click="deleteImage({{ $media->id }})"
                            class="absolute top-1 right-1 bg-black/50 text-white text-xs rounded-full px-1 opacity-0 group-hover:opacity-100 transition">
                            ×
                        </button>
                    </div>
                @endforeach
            @endif

            {{-- Imágenes pendientes (no guardadas aún) --}}
            @if ($pendingImages)
                @foreach ($pendingImages as $index => $preview)
                    <div class="relative group">
                        <img src="{{ $preview->temporaryUrl() }}"
                            class="h-20 w-20 object-cover rounded-lg border border-gray-200 dark:border-neutral-700 opacity-70" />
                        <div
                            class="absolute inset-0 flex items-center justify-center text-gray-700 dark:text-neutral-300 opacity-0 group-hover:opacity-100 transition">
                            <x-icons.lucide.clock class="h-5 w-5" />
                        </div>
                        <button type="button" wire:click="removePending({{ $index }})"
                            class="absolute top-1 right-1 bg-black/50 text-white text-xs rounded-full px-1 opacity-0 group-hover:opacity-100 transition">
                            ×
                        </button>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Contador --}}
        <p class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
            {{ count($pendingImages) + ($exercise?->getMedia('images')->count() ?? 0) }}/{{$maxFiles}}
            {{ __('common.images_uploaded') }}
        </p>
    </div>


    {{-- Slots opcionales para acciones extra --}}
    @slot('actions')
        {{-- <flux:button size="sm" variant="ghost">Ayuda</flux:button> --}}
    @endslot

    @slot('footerActions')
        {{-- <flux:button size="sm" variant="ghost">Exportar</flux:button> --}}
    @endslot
</x-simple-form>
