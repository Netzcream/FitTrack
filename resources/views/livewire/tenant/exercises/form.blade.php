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



    {{-- Slots opcionales para acciones extra --}}
    @slot('actions')
        {{-- <flux:button size="sm" variant="ghost">Ayuda</flux:button> --}}
    @endslot

    @slot('footerActions')
        {{-- <flux:button size="sm" variant="ghost">Exportar</flux:button> --}}
    @endslot
</x-simple-form>
