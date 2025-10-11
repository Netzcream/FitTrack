<div class="space-y-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl" level="1">{{ __('Entrenamiento') }}</flux:heading>



        <flux:button as="a"
            href="{{ route('tenant.dashboard.exercises.plans.assign.wizard', ['student_id' => $student->id]) }}">
            {{ __('Asignar rutina') }}
        </flux:button>

    </div>

    <flux:subheading size="lg" class="text-muted-foreground">
        {{ __('Gestión de rutinas asignadas, sesiones y adherencia.') }}
    </flux:subheading>

    <flux:separator variant="subtle" />

    <div class="rounded-xl border dark:border-zinc-700 p-6 text-sm text-muted-foreground">
        {{ __('No hay rutinas asignadas aún. Usá “Asignar rutina” para comenzar.') }}
    </div>
</div>
