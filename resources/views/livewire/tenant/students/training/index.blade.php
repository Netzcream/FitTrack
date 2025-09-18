<div class="space-y-4">
  <div class="flex items-center justify-between">
    <flux:heading size="xl" level="1">{{ __('Entrenamiento') }}</flux:heading>
    <a href="{{ route('tenant.dashboard.students.training', $student) }}"
       class="inline-flex items-center rounded-md px-3 py-2 border bg-primary/10 text-primary hover:bg-primary/15">
      {{ __('Asignar rutina') }}
    </a>
  </div>

  <flux:subheading size="lg" class="text-muted-foreground">
    {{ __('Gestión de rutinas asignadas, sesiones y adherencia.') }}
  </flux:subheading>

  <flux:separator variant="subtle" />

  <div class="rounded-xl border p-6 text-sm text-muted-foreground">
    {{ __('No hay rutinas asignadas aún. Usá “Asignar rutina” para comenzar.') }}
  </div>
</div>
