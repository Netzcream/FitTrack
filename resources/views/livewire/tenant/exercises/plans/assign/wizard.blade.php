<div class="space-y-6">
    <flux:heading size="xl">{{ __('Asignar rutina a alumno') }}</flux:heading>
    <flux:separator variant="subtle" />

    @switch($step)
        @case(1)
            <livewire:tenant.exercises.plans.assign.steps.select-student
                wire:key="step-1"
                :state="$state"
                :step="$step" />
        @break

        @case(2)
            <livewire:tenant.exercises.plans.assign.steps.select-template
                wire:key="step-2"
                :state="$state" />
        @break

        @case(3)
            <livewire:tenant.exercises.plans.assign.steps.setup
                wire:key="step-3"
                :state="$state" />
        @break

        @case(4)
            <livewire:tenant.exercises.plans.assign.steps.confirm
                wire:key="step-4"
                :state="$state" />
        @break
    @endswitch
</div>
