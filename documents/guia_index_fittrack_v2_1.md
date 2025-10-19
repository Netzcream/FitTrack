# Guía de diseño de *Index* (listados) — Estándar Unificado **v2.1** (FitTrack)

> **Objetivo**: alinear todos los listados (Students, Payments, Plans, etc.) a **cómo los usamos hoy** con Livewire + Flux + **componente obligatorio** `<x-data-table>`.  
> Esta versión corrige estilos/bordes, estructura, manejo de modal único para eliminar, `wire:key`, `divide-y`, y agrega ejemplos de back (query, filtros por FK y Enum, paginación, borrar).

---

## 0) Principios rápidos

- **Componente obligatorio:** todos los índices usan `<x-data-table>` con sus *slots*: `filters`, `head`, `modal` y el `slot` principal (filas).  
- **Estética:** tabla **sin sombras**, **un solo borde** en el contenedor, `divide-y` en `tbody`, textos neutros y dark mode consistente.  
- **UX:** filtros arriba, CTA “Nuevo” a la derecha, **un único modal** de confirmación de borrado (fuera del bucle), `wire:key` por fila.  
- **Back:** búsquedas agrupadas, filtros por FK/Enum, ordenamiento seguro y **paginación**.

---

## 1) Contenedor, Header y CTA

```blade
<div class="flex items-start max-md:flex-col">
  <div class="flex-1 self-stretch max-md:pt-6 space-y-6">
    {{-- Header --}}
    <div class="relative mb-6 w-full">
      <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
          <flux:heading size="xl" level="1">{{ __('<ns>.index_title') }}</flux:heading>
          <flux:subheading size="lg" class="mb-6">{{ __('<ns>.index_subheading') }}</flux:subheading>
        </div>

        <flux:button as="a" href="{{ route('tenant.dashboard.<entity>.create') }}" variant="primary" icon="plus">
          {{ __('<ns>.new_entity') }}
        </flux:button>
      </div>
      <flux:separator variant="subtle" />
    </div>
```

> Mantener `heading level="1"`. El CTA **siempre** a la derecha, `variant="primary"`.

---

## 2) **Componente** `<x-data-table>` (mandatorio)

**API (resumen):**
```blade
@props(['pagination' => null, 'filters' => null, 'head' => null, 'modal' => null])

{{-- Uso: --}}
<x-data-table :pagination="$collection">
  <x-slot name="filters">…</x-slot>
  <x-slot name="head">…</x-slot>

  {{-- Filas --}}
  …tr/td…

  <x-slot name="modal">…modal único…</x-slot>
</x-data-table>
```

**Requisitos visuales del componente:**

- El contenedor de la tabla debe tener **un solo borde** y `overflow-hidden`.  
  ➜ **Clases correctas**: `border border-gray-200 dark:border-neutral-700`  
  (Si tu versión tiene `border-zinc-700`, **actualizarla** para ser consistente con el resto de la guía).

- El `<tbody>` debe tener `class="divide-y divide-gray-200 dark:divide-neutral-700"`.

- La paginación se muestra **debajo** de la tabla.

---

## 3) Filtros (coherentes con relaciones y enums)

```blade
<x-slot name="filters">
  <div class="flex flex-wrap gap-4 w-full items-end">
    <div class="max-w-[260px] flex-1">
      <flux:input size="sm" class="w-full"
        wire:model.live.debounce.250ms="q"
        :label="__('common.search')"
        placeholder="{{ __('<ns>.search_placeholder') }}" />
    </div>

    {{-- Enum: status --}}
    <div class="min-w-[160px]">
      <flux:select size="sm" wire:model.live="status" :label="__('<ns>.status')">
        <option value="">{{ __('common.all') }}</option>
        {{-- Valores string coherentes con el Enum del modelo --}}
        <option value="active">{{ __('<ns>.status.active') }}</option>
        <option value="paused">{{ __('<ns>.status.paused') }}</option>
        <option value="inactive">{{ __('<ns>.status.inactive') }}</option>
        <option value="prospect">{{ __('<ns>.status.prospect') }}</option>
      </flux:select>
    </div>

    {{-- FK: plan comercial --}}
    <div class="min-w-[160px]">
      <flux:select size="sm" wire:model.live="plan" :label="__('<ns>.plan')">
        <option value="">{{ __('common.all') }}</option>
        @foreach ($plans as $id => $name)
          <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
      </flux:select>
    </div>

    <div>
      <flux:button size="sm" variant="ghost" wire:click="resetFilters">
        {{ __('common.clear') }}
      </flux:button>
    </div>
  </div>
</x-slot>
```

> **Reglas:**  
> - Texto: `wire:model.live.debounce.250ms`.  
> - Selects: `wire:model.live`.  
> - `resetPage()` al cambiar filtros (ver back).  
> - **Valores** de `status` deben coincidir con el **Enum/string real** del Modelo.

---

## 4) Cabecera de tabla (sorting)

```blade
<x-slot name="head">
  <th wire:click="sort('last_name')"
      class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
    <span class="inline-flex items-center gap-1">
      {{ __('<ns>.name') }}
      @if ($sortBy === 'last_name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
    </span>
  </th>

  <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
    {{ __('<ns>.status') }}
  </th>

  <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
    {{ __('<ns>.plan') }}
  </th>

  <th wire:click="sort('last_login_at')"
      class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
    {{ __('<ns>.last_login_at') }}
  </th>

  <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
    {{ __('common.actions') }}
  </th>
</x-slot>
```

---

## 5) Cuerpo de tabla (identidad, badges, acciones)

```blade
@forelse ($items as $item)
  <tr wire:key="<entity>-{{ $item->uuid }}" class="divide-y divide-gray-200 dark:divide-neutral-700">
    {{-- Identidad: avatar + nombre + email --}}
    <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
      <div class="inline-flex items-center gap-3">
        <div class="h-8 w-8 rounded-full overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 flex items-center justify-center">
          @if ($item->hasMedia('avatar'))
            <img src="{{ $item->getFirstMediaUrl('avatar', 'thumb') }}" alt="{{ $item->full_name }}" class="object-cover h-full w-full">
          @else
            <span class="text-xs font-semibold">{{ strtoupper(substr($item->first_name,0,1).substr($item->last_name,0,1)) }}</span>
          @endif
        </div>
        <div class="leading-tight">
          <div class="font-medium text-gray-900 dark:text-neutral-100">{{ $item->full_name }}</div>
          <div class="text-xs text-gray-500 dark:text-neutral-400">{{ $item->email }}</div>
        </div>
      </div>
    </td>

    {{-- Badge de estado (Enum string) --}}
    <td class="align-top px-6 py-4 text-sm">
      @php
        $state = $item->status;
        $styles = [
          'active'   => 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-950/40 dark:text-green-300 dark:ring-green-900',
          'paused'   => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-900',
          'inactive' => 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800',
          'prospect' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-900',
        ];
      @endphp
      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $styles[$state] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800' }}">
        {{ __('<ns>.status.' . $state) }}
      </span>
    </td>

    {{-- FK: plan --}}
    <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
      {{ $item->commercialPlan?->name ?? '—' }}
    </td>

    {{-- Fecha --}}
    <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
      {{ $item->last_login_at?->format('d/m/Y H:i') ?? '—' }}
    </td>

    {{-- Acciones --}}
    <td class="align-top px-6 py-4 text-end text-sm font-medium">
      <span class="inline-flex items-center gap-2 text-xs text-gray-400 dark:text-neutral-500 whitespace-nowrap">
        <flux:button size="sm" as="a" wire:navigate href="{{ route('tenant.dashboard.<entity>.edit', $item->uuid) }}">
          {{ __('common.edit') }}
        </flux:button>

        {{-- Trigger → **un solo modal** declarado en el slot "modal" --}}
        <flux:modal.trigger name="confirm-delete-<entity>">
          <flux:button size="sm" variant="ghost" wire:click="confirmDelete('{{ $item->uuid }}')">
            {{ __('common.delete') }}
          </flux:button>
        </flux:modal.trigger>
      </span>
    </td>
  </tr>
@empty
  <tr>
    <td colspan="100" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
      {{ __('common.empty_state') }}
    </td>
  </tr>
@endforelse
```

**Modal único (fuera del loop):**
```blade
<x-slot name="modal">
  <flux:modal name="confirm-delete-<entity>" class="min-w-[22rem]" x-data
    @<entity>-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-<entity>' })">
    <div class="space-y-6">
      <div>
        <flux:heading size="lg">{{ __('common.delete_title') }}</flux:heading>
        <flux:text class="mt-2">{{ __('common.delete_msg') }}</flux:text>
      </div>
      <div class="flex gap-2">
        <flux:spacer />
        <flux:modal.close>
          <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
        </flux:modal.close>
        <flux:button wire:click="delete" variant="danger">
          {{ __('common.confirm_delete') }}
        </flux:button>
      </div>
    </div>
  </flux:modal>
</x-slot>
```

---

## 6) **Ejemplo completo** con `<x-data-table>` (Students real)

> Este ejemplo refleja el uso actual en *Students* y sirve de plantilla para otros listados.

```blade
<section class="w-full">
  <x-data-table :pagination="$students">
    {{-- Filtros --}}
    <x-slot name="filters">
      <div class="flex flex-wrap gap-4 w-full items-end">
        <div class="max-w-[260px] flex-1">
          <flux:input size="sm" class="w-full" wire:model.live.debounce.250ms="q"
            :label="__('common.search')" placeholder="{{ __('students.search_placeholder') }}" />
        </div>

        <div class="min-w-[160px]">
          <flux:select size="sm" wire:model.live="status" :label="__('students.status')">
            <option value="">{{ __('common.all') }}</option>
            <option value="active">{{ __('students.status.active') }}</option>
            <option value="paused">{{ __('students.status.paused') }}</option>
            <option value="inactive">{{ __('students.status.inactive') }}</option>
            <option value="prospect">{{ __('students.status.prospect') }}</option>
          </flux:select>
        </div>

        <div class="min-w-[160px]">
          <flux:select size="sm" wire:model.live="plan" :label="__('students.plan')">
            <option value="">{{ __('common.all') }}</option>
            @foreach ($plans as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
          </flux:select>
        </div>

        <div>
          <flux:button size="sm" variant="ghost" wire:click="resetFilters">
            {{ __('common.clear') }}
          </flux:button>
        </div>
      </div>
    </x-slot>

    {{-- Head --}}
    <x-slot name="head">
      {{-- (igual al bloque del punto 4) --}}
      {{-- ... --}}
    </x-slot>

    {{-- Filas (igual al punto 5) --}}
    {{-- ... --}}

    {{-- Modal único --}}
    <x-slot name="modal">
      {{-- (igual al modal del punto 5) con name="confirm-delete-student" --}}
    </x-slot>
  </x-data-table>
</section>
```

---

## 7) Back (Livewire) — Query, filtros, orden, borrado y paginación

```php
namespace App\Livewire\Tenant\Students;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Student;
use App\Models\Tenant\CommercialPlan;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    // Filtros
    public string $q = '';
    public ?string $status = null;   // Enum string: active/paused/inactive/prospect
    public ?int $plan = null;        // FK: commercial_plan_id

    // Orden/paginación
    public string $sortBy = 'last_name';
    public string $sortDirection = 'asc';
    public int $perPage = 10;

    // Eliminar
    public ?string $deleteUuid = null;

    /** Reset de página ante cualquier cambio relevante */
    public function updated($field): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['q', 'status', 'plan']);
    }

    public function confirmDelete(string $uuid): void
    {
        $this->deleteUuid = $uuid;
    }

    public function delete(): void
    {
        if ($this->deleteUuid) {
            Student::where('uuid', $this->deleteUuid)->delete();
            $this->deleteUuid = null;
            $this->dispatch('student-deleted'); // cierra modal
        }
    }

    public function render()
    {
        $students = Student::query()
            ->with('commercialPlan')
            // Búsqueda agrupada para evitar precedencia errónea de OR
            ->when($this->q, function ($q) {
                $t = "%{$this->q}%";
                $q->where(function ($qq) use ($t) {
                    $qq->where('first_name', 'like', $t)
                       ->orWhere('last_name', 'like', $t)
                       ->orWhere('email', 'like', $t);
                });
            })
            // Enum string exacto
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            // FK plan
            ->when($this->plan, fn ($q) => $q->where('commercial_plan_id', $this->plan))
            // Orden seguro
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.students.index', [
            'students' => $students,
            'plans'    => CommercialPlan::orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
```

> **Notas de back:**  
> - La búsqueda está **agrupada** en un `where(function(){…})` para no romper filtros por la precedencia con `orWhere`.  
> - Los valores de `status` deben coincidir con el **Enum/string** del modelo.  
> - `resetPage()` en `updated()` asegura paginación correcta al filtrar/ordenar.

---

## 8) Colores, bordes y dark mode (resumen)

- Contenedor tabla: `overflow-hidden border border-gray-200 dark:border-neutral-700 rounded-lg`.
- Separadores internos: `divide-gray-200 dark:divide-neutral-700`.
- Texto principal: `text-gray-800 dark:text-neutral-200`.
- Encabezados: `text-gray-500 dark:text-neutral-500`, `text-xs uppercase`.
- Badges: usar paletas suaves con `*-950/40` en dark.

---

## 9) Checklist de revisión (v2.1)

- [ ] **Usa `<x-data-table>`** con slots `filters`, `head`, `modal` y `slot` principal.  
- [ ] **Header** con `heading/subheading` y CTA `primary` a la derecha.  
- [ ] **Filtros**: input `debounce.250ms`, selects `live`, botón **Limpiar** `ghost`.  
- [ ] **Tabla sin sombras**; **borde único** en contenedor; `tbody` con `divide-y`.  
- [ ] **wire:key** por fila y clases de fila `divide-y` correctas.  
- [ ] **Modal de borrar único** (en slot `modal`), abierto con `flux:modal.trigger` desde cada fila; botón de confirmación `variant="danger"`.  
- [ ] **Búsqueda agrupada** en back; filtros por **FK** y **Enum** coherentes.  
- [ ] **Paginación** activa y `resetPage()` al cambiar filtros/orden.  
- [ ] **i18n** consistente (`common.*`, `<ns>.*`) en labels, placeholders y estados.  
- [ ] Estilos del componente actualizados a `border-gray-200 / dark:border-neutral-700`.

---

> **Uso:** Copiá/pegá estos bloques como plantilla. Si el listado tiene particularidades (orden manual, acciones masivas), extendelo, pero **sin romper** el contrato visual y de slots del componente `<x-data-table>`.
