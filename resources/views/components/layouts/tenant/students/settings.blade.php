<x-layouts.tenant>
@php
  $active            = $active ?? 'training';
  $overdueInvoices   = $overdueInvoices ?? 0;
  $aptExpiresInDays  = $aptExpiresInDays ?? null;
  $unreadMessages    = $unreadMessages ?? 0;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-[16rem_1fr] gap-8">
  <aside class="space-y-4">
    <div class="p-4 rounded-xl border">
      <div class="flex items-center gap-3">
        @if($student?->hasMedia('avatar'))
          <img src="{{ $student->getFirstMediaUrl('avatar','thumb') }}" alt="{{ $student->full_name }}" class="h-12 w-12 rounded-full object-cover">
        @else
          @php $i1 = mb_substr($student->first_name ?? '',0,1); $i2 = mb_substr($student->last_name ?? '',0,1); @endphp
          <div class="h-12 w-12 rounded-full bg-muted flex items-center justify-center text-sm font-semibold">
            {{ trim($i1.$i2) ?: '??' }}
          </div>
        @endif
        <div class="min-w-0">
          <div class="font-semibold truncate">{{ $student->full_name }}</div>
          <div class="text-xs text-muted-foreground">{{ __('ID') }}: {{ \Illuminate\Support\Str::limit($student->uuid, 8, '') }}</div>
          <div class="text-xs mt-0.5">
            <span class="inline-flex items-center gap-1">
              <span class="inline-block h-2 w-2 rounded-full {{ ($student->status ?? 'inactive') === 'active' ? 'bg-green-500' : 'bg-gray-400' }}"></span>
              {{ __($student->status ?? 'inactive') }}
            </span>
          </div>
        </div>
      </div>
    </div>

    @php
      $items = [
        ['key'=>'training','label'=>__('Entrenamiento'),'href'=>route('tenant.dashboard.students.training',$student),'badge'=>null],
        ['key'=>'profile','label'=>__('Datos personales'),'href'=>route('tenant.dashboard.students.profile',$student),'badge'=>null],
        ['key'=>'finance','label'=>__('Finanzas'),'href'=>route('tenant.dashboard.students.finance',$student),'badge'=>$overdueInvoices ?: null],
        ['key'=>'health','label'=>__('Salud & apto'),'href'=>route('tenant.dashboard.students.health',$student),'badge'=>($aptExpiresInDays !== null && $aptExpiresInDays <= 7) ? __('¡Vence!') : null],
        ['key'=>'metrics','label'=>__('Métricas'),'href'=>route('tenant.dashboard.students.metrics',$student),'badge'=>null],
        ['key'=>'files','label'=>__('Archivos'),'href'=>route('tenant.dashboard.students.files',$student),'badge'=>null],
        ['key'=>'messages','label'=>__('Mensajes'),'href'=>route('tenant.dashboard.students.messages',$student),'badge'=>$unreadMessages ?: null],
      ];
    @endphp

    <nav class="space-y-1">
      <a href="{{ route('tenant.dashboard.students.index') }}" class="block px-3 py-2 text-xs text-muted-foreground hover:underline">
        ← {{ __('Volver a alumnos') }}
      </a>
      @foreach ($items as $it)
        <a href="{{ $it['href'] }}"
           class="flex items-center justify-between px-3 py-2 rounded-md transition
                  {{ $active === $it['key'] ? 'bg-muted font-medium' : 'hover:bg-muted/60' }}">
          <span>{{ $it['label'] }}</span>
          @if($it['badge'])
            <span class="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary">{{ $it['badge'] }}</span>
          @endif
        </a>
      @endforeach
    </nav>
  </aside>

  <main class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="p-4 rounded-xl border">
        <div class="text-xs text-muted-foreground">{{ __('IMC') }}</div>
        <div class="text-lg font-semibold">{{ $student->imc ?? '—' }}</div>
      </div>
      <div class="p-4 rounded-xl border">
        <div class="text-xs text-muted-foreground">{{ __('Apto físico') }}</div>
        <div class="text-lg font-semibold">
          @if($student->apt_fitness_expires_at)
            {{ __('Vence el') }} {{ $student->apt_fitness_expires_at->isoFormat('D MMM') }}
          @else
            —
          @endif
        </div>
      </div>
      <div class="p-4 rounded-xl border">
        <div class="text-xs text-muted-foreground">{{ __('Último acceso') }}</div>
        <div class="text-lg font-semibold">{{ optional($student->last_login_at)->diffForHumans() ?? '—' }}</div>
      </div>
    </div>

    {{ $slot }}
  </main>
</div>

</x-layouts.tenant>
