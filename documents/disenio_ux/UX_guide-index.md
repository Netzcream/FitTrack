---
title: Guía de diseño de Index (listados) — Estándar Unificado
version: 2.2
last_updated: 2026-01-02
author: Up2U Team
---

# Guía de diseño de *Index* (listados) — Estándar Unificado

> **Objetivo**: alinear todos los listados (Students, Payments, Plans, Countries, Carriers, Airports, etc.) a **cómo los usamos hoy** con Livewire + Flux + **componentes reutilizables**.  
> Esta versión incluye el componente `<x-index-filters>` para estandarizar filtros, junto con `<x-data-table>`, estilos/bordes consistentes, modal único para eliminar, `wire:key`, `divide-y`, y ejemplos completos de backend.

---

## 0) Principios rápidos

- **Componentes obligatorios:** 
  - `<x-data-table>` con slots: `filters`, `head`, `modal` y slot principal (filas)
  - `<x-index-filters>` para filtros estandarizados (nuevo en v2.2)
- **Estética:** tabla **sin sombras**, **un solo borde** en el contenedor, `divide-y` en `tbody`, textos neutros y dark mode consistente.  
- **UX:** filtros arriba con componente reutilizable, CTA "Nuevo" a la derecha, **un único modal** de confirmación de borrado (fuera del bucle), `wire:key` por fila.  
- **Back:** búsquedas agrupadas, filtros por FK/Enum, ordenamiento seguro y **paginación**.
- **Paginación:** Usar Preliné:         {{ $this->collection->links('components.preline.pagination') }}
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

## 3) **Componente** `<x-index-filters>` (nuevo en v2.2)

Este componente **estandariza los filtros** en todos los índices, proporcionando:
- Campo de búsqueda consistente
- Slot para filtros adicionales (FK, Enums, etc.)
- Botón "Limpiar" estandarizado
- UX uniforme en toda la aplicación

### API del componente:

```blade
@props([
    'search' => '',
    'searchPlaceholder' => 'Buscar...',
    'additionalFilters' => null,
])
```

### Uso básico (solo búsqueda):

```blade
<x-slot name="filters">
  <x-index-filters :searchPlaceholder="__('Buscar por nombre o código...')" />
</x-slot>
```

### Uso con filtros adicionales:

```blade
<x-slot name="filters">
  <x-index-filters :searchPlaceholder="__('Buscar por código, nombre o ciudad...')">
    <x-slot name="additionalFilters">
      {{-- Filtro por País (FK) --}}
      <div class="max-w-[200px]">
        <flux:select size="sm" label="{{ __('País') }}" wire:model.live="countryFilter">
          <option value="">{{ __('Todos') }}</option>
          @foreach ($countries as $country)
            <flux:select.option value="{{ $country->code }}">{{ $country->name }}</flux:select.option>
          @endforeach
        </flux:select>
      </div>

      {{-- Filtro por Enum: status --}}
      <div class="min-w-[160px]">
        <flux:select size="sm" wire:model.live="status" :label="__('<ns>.status')">
          <option value="">{{ __('common.all') }}</option>
          <option value="active">{{ __('<ns>.status.active') }}</option>
          <option value="paused">{{ __('<ns>.status.paused') }}</option>
          <option value="inactive">{{ __('<ns>.status.inactive') }}</option>
        </flux:select>
      </div>
    </x-slot>
  </x-index-filters>
</x-slot>
```

### Ventajas del componente:
- ✨ **UX consistente** en todos los índices
- 🔧 **Fácil mantenimiento** - cambios en un solo lugar
- ♻️ **Reutilizable** - una línea para agregar filtros estándar
- 🎯 **Flexible** - acepta filtros adicionales vía slots

---

## 4) Filtros (coherentes con relaciones y enums) — Versión tradicional

> **Nota:** Se recomienda usar `<x-index-filters>` (punto 3), pero si necesitas una implementación personalizada, aquí está el patrón tradicional:

```blade
<x-slot name="filters">
  <div class="flex flex-wrap gap-4 w-full items-end">
    <div class="max-w-[260px] flex-1">
      <flux:input size="sm" class="w-full"
        wire:model.live.debounce.250ms="search"
        :label="__('common.search')"
        placeholder="{{ __('<ns>.search_placeholder') }}" />
    </div>

    {{-- Enum: status --}}
    <div class="min-w-[160px]">
      <flux:select size="sm" wire:model.live="status" :label="__('<ns>.status')">
        <option value="">{{ __('common.all') }}</option>
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
      <flux:button size="sm" variant="ghost" wire:click="clearFilters">
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

## 5) Cabecera de tabla (sorting)

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

## 6) Cuerpo de tabla (identidad, badges, acciones)

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

## 7) **Ejemplos completos con** `<x-data-table>` y `<x-index-filters>`

### Ejemplo 1: Index simple (Countries)

```blade
<section class="w-full">
  <x-data-table :pagination="$countries">
    {{-- Filtros con componente --}}
    <x-slot name="filters">
      <x-index-filters :searchPlaceholder="__('Buscar por nombre o código...')" />
    </x-slot>

    {{-- Head --}}
    <x-slot name="head">
      <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-start">
        {{ __('Código ISO') }}
      </th>
      <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-start">
        {{ __('Nombre') }}
      </th>
      <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
        {{ __('common.actions') }}
      </th>
    </x-slot>

    {{-- Filas --}}
    @forelse ($countries as $country)
      <tr wire:key="country-{{ $country->id }}">
        <td class="align-top px-6 py-4 text-sm">
          <span class="inline-flex items-center rounded-md bg-blue-100 dark:bg-blue-900/40 px-2 py-1 text-xs font-semibold text-blue-800 dark:text-blue-300">
            {{ $country->code }}
          </span>
        </td>
        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200 font-medium">
          {{ $country->name }}
        </td>
        <td class="align-top px-6 py-4 text-end text-sm font-medium">
          <span class="inline-flex items-center gap-2">
            <flux:button size="sm" as="a" wire:navigate href="{{ route('central.dashboard.countries.edit', $country->code) }}">
              {{ __('common.edit') }}
            </flux:button>
          </span>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="3" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
          {{ __('No se encontraron países.') }}
        </td>
      </tr>
    @endforelse

    {{-- Modal --}}
    <x-slot name="modal">
      {{-- Modal de confirmación si es necesario --}}
    </x-slot>
  </x-data-table>
</section>
```

### Ejemplo 2: Index con filtro adicional (Airports)

```blade
<section class="w-full">
  <x-data-table :pagination="$airports">
    {{-- Filtros con componente + filtro de país --}}
    <x-slot name="filters">
      <x-index-filters :searchPlaceholder="__('Buscar por código, nombre o ciudad...')">
        <x-slot name="additionalFilters">
          <div class="max-w-[200px]">
            <flux:select size="sm" label="{{ __('País') }}" wire:model.live="countryFilter">
              <option value="">{{ __('Todos') }}</option>
              @foreach ($countries as $country)
                <flux:select.option value="{{ $country->code }}">{{ $country->name }}</flux:select.option>
              @endforeach
            </flux:select>
          </div>
        </x-slot>
      </x-index-filters>
    </x-slot>

    {{-- Head --}}
    <x-slot name="head">
      <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-start">
        {{ __('Código IATA') }}
      </th>
      <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-start">
        {{ __('Nombre') }}
      </th>
      <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-center">
        {{ __('País') }}
      </th>
      <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
        {{ __('Acciones') }}
      </th>
    </x-slot>

    {{-- Filas --}}
    @forelse ($airports as $airport)
      <tr wire:key="airport-{{ $airport->id }}">
        {{-- ... contenido de la fila ... --}}
      </tr>
    @empty
      <tr>
        <td colspan="4" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
          {{ __('No se encontraron aeropuertos.') }}
        </td>
      </tr>
    @endforelse

    <x-slot name="modal">
      {{-- Modal de confirmación --}}
    </x-slot>
  </x-data-table>
</section>
```

### Ejemplo 3: Index completo con múltiples filtros (Students)

```blade
<section class="w-full">
  <x-data-table :pagination="$students">
    {{-- Filtros con componente + filtros de status y plan --}}
    <x-slot name="filters">
      <x-index-filters :searchPlaceholder="__('students.search_placeholder')">
        <x-slot name="additionalFilters">
          {{-- Filtro por status (Enum) --}}
          <div class="min-w-[160px]">
            <flux:select size="sm" wire:model.live="status" :label="__('students.status')">
              <option value="">{{ __('common.all') }}</option>
              <option value="active">{{ __('students.status.active') }}</option>
              <option value="paused">{{ __('students.status.paused') }}</option>
              <option value="inactive">{{ __('students.status.inactive') }}</option>
              <option value="prospect">{{ __('students.status.prospect') }}</option>
            </flux:select>
          </div>

          {{-- Filtro por plan (FK) --}}
          <div class="min-w-[160px]">
            <flux:select size="sm" wire:model.live="plan" :label="__('students.plan')">
              <option value="">{{ __('common.all') }}</option>
              @foreach ($plans as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </flux:select>
          </div>
        </x-slot>
      </x-index-filters>
    </x-slot>

    {{-- Head y filas según sección 5 y 6 --}}
    {{-- ... --}}

  </x-data-table>
</section>
```

---

## 8) Back (Livewire) — Query, filtros, orden, borrado y paginación

```php
namespace App\Livewire\Central\Airport;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Central\Airport;
use App\Models\Central\Country;

#[Layout('components.layouts.app', [
    'title' => 'Aeropuertos',
])]
class Index extends Component
{
    use WithPagination;

    // Filtros (debe coincidir con el wire:model del componente)
    public string $search = '';          // campo estándar del componente <x-index-filters>
    public string $countryFilter = '';   // filtro adicional

    // Eliminar
    public ?int $deletingId = null;

    /** Reset de página ante cualquier cambio relevante */
    public function updated($field): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'countryFilter']);
        $this->resetPage();
    }

    public function confirmDelete($id): void
    {
        $this->deletingId = $id;
        $this->dispatch('modal-open', name: 'confirm-delete-airport');
    }

    public function delete(): void
    {
        if (!$this->deletingId) {
            return;
        }

        try {
            $airport = Airport::findOrFail($this->deletingId);
            $airport->delete();

            $this->dispatch('notify', message: __('Aeropuerto eliminado correctamente.'), type: 'success');
            $this->dispatch('airport-deleted');
        } catch (\Throwable $e) {
            $message = __('Error al eliminar el aeropuerto: ') . $e->getMessage();
            $this->dispatch('notify', message: $message, type: 'error');
        }

        $this->reset('deletingId');
    }

    public function render()
    {
        $airports = Airport::query()
            // Búsqueda agrupada
            ->when($this->search, function ($q) {
                $t = "%{$this->search}%";
                $q->where(function ($qq) use ($t) {
                    $qq->where('name', 'like', $t)
                       ->orWhere('iata_code', 'like', $t)
                       ->orWhere('city', 'like', $t);
                });
            })
            // Filtro por país
            ->when($this->countryFilter, fn ($q) => $q->where('country_code', $this->countryFilter))
            ->orderBy('name')
            ->paginate(15);

        $countries = Country::orderBy('name')->get(['code', 'name']);

        return view('livewire.central.airport.index', [
            'airports' => $airports,
            'countries' => $countries,
        ]);
    }
}
```

> **Notas críticas de back:**  
> - El nombre de la propiedad pública debe ser **`search`** (no `q`) para funcionar con `<x-index-filters>`.
> - La búsqueda está **agrupada** en un `where(function(){…})` para evitar problemas de precedencia con `orWhere`.
> - `updated()` llama a `resetPage()` automáticamente al cambiar cualquier filtro.
> - El método `clearFilters()` debe resetear todos los filtros usados.

---

## 9) Colores, bordes y dark mode (resumen)

- Contenedor tabla: `overflow-hidden border border-gray-200 dark:border-neutral-700 rounded-lg`.
- Separadores internos: `divide-gray-200 dark:divide-neutral-700`.
- Texto principal: `text-gray-800 dark:text-neutral-200`.
- Encabezados: `text-gray-500 dark:text-neutral-500`, `text-xs uppercase`.
- Badges: usar paletas suaves con `*-950/40` en dark.

---

## 10) Checklist de revisión (v2.2)

- [ ] **Usa `<x-data-table>`** con slots `filters`, `head`, `modal` y `slot` principal.
- [ ] **Usa `<x-index-filters>`** para los filtros (consistencia garantizada).
- [ ] **Header** con `heading/subheading` y CTA `primary` a la derecha.
- [ ] **Filtros**: 
  - Campo de búsqueda usa `wire:model.live.debounce.250ms="search"`
  - Filtros adicionales usan `wire:model.live`
  - Botón **Limpiar** con `variant="ghost"` y método `clearFilters()`
- [ ] **Tabla sin sombras**; **borde único** en contenedor; `tbody` con `divide-y`.
- [ ] **wire:key** por fila con ID único.
- [ ] **Modal de borrar único** (en slot `modal`), abierto con `flux:modal.trigger` desde cada fila; botón de confirmación `variant="danger"`.
- [ ] **Backend:**
  - Propiedad pública `public string $search = '';` (no `$q`)
  - Búsqueda agrupada con `where(function(){…})`
  - Filtros por **FK** y **Enum** coherentes
  - `updated()` llama a `resetPage()`
  - Método `clearFilters()` implementado
- [ ] **Paginación** activa.
- [ ] **i18n** consistente (`common.*`, `<ns>.*`) en labels, placeholders y estados.
- [ ] Estilos actualizados a `border-gray-200 / dark:border-neutral-700`.

---

## 11) Implementación del componente `<x-index-filters>`

**Ubicación:** `resources/views/components/index-filters.blade.php`

```blade
@props([
    'search' => '',
    'searchPlaceholder' => 'Buscar...',
    'additionalFilters' => null,
])

<div class="flex flex-wrap gap-4 w-full items-end">
    {{-- Campo de búsqueda --}}
    <div class="max-w-[260px] flex-1">
        <flux:input 
            size="sm" 
            class="w-full"
            wire:model.live.debounce.250ms="search"
            label="{{ __('Buscar') }}"
            placeholder="{{ $searchPlaceholder }}" />
    </div>

    {{-- Filtros adicionales (slot opcional) --}}
    @if($additionalFilters)
        {{ $additionalFilters }}
    @endif

    {{-- Botón de limpiar filtros --}}
    <div>
        <flux:button size="sm" variant="ghost" wire:click="clearFilters">
            {{ __('common.clear') }}
        </flux:button>
    </div>
</div>
```

---

## Historial de versiones

### v2.2 (2026-01-02)
- ✨ **Nuevo:** Componente `<x-index-filters>` para estandarizar filtros
- 📝 Ejemplos actualizados con uso de `<x-index-filters>`
- 🔧 Estandarización de nombres de propiedades (`search` en lugar de `q`)
- 📚 Tres ejemplos completos: Countries, Airports, Students
- 📖 Documentación mejorada de backend con nombres consistentes

### v2.1
- Corrección de estilos/bordes
- Modal único para eliminar
- `wire:key` y `divide-y`
- Ejemplos de backend (query, filtros, paginación)

---

> **Uso:** Este documento es la **fuente de verdad** para todos los listados. Copiá/pegá estos bloques como plantilla. Si el listado tiene particularidades (orden manual, acciones masivas), extendelo, pero **sin romper** el contrato visual y de slots de los componentes `<x-data-table>` y `<x-index-filters>`.
