# Guía de diseño de *Index* (listados) — Estándar Unificado v2.0 (FitTrack)

> **Objetivo**: documentar, de forma precisa y reusable, cómo debe verse y comportarse **cualquier listado (Index)** de la aplicación. Esta versión 2.0 incluye mejoras de consistencia con la guía de *Formularios Simples*, los ejemplos reales (Students / CommercialPlans) y las nuevas convenciones Livewire + Flux.

---

## 1) Anatomía del index

**Bloques principales (de arriba hacia abajo):**
1. **Header**: título, subtítulo y CTA principal (ej. “Nuevo …”).
2. **Separador sutil**.
3. **Bloque de filtros** (buscador + selects + reset) — *siempre presente*.
4. **Barra de acciones contextual (opcional)**: para acciones masivas o globales.
5. **Tabla principal**: `thead`, `tbody`, `tfoot` opcional.
6. **Paginación** (cuando corresponda).
7. **Modales** (confirmaciones o creación).

**Contenedor general**
- Wrapper: `div.flex.items-start.max-md:flex-col`
- Columna principal: `div.flex-1.self-stretch.max-md:pt-6.space-y-6`

> Mantener **espaciado vertical consistente (`space-y-6`)** entre secciones.

---

## 2) Header

```blade
<div class="relative mb-6 w-full">
  <div class="flex items-center justify-between gap-4 flex-wrap">
    <div>
      <flux:heading size="xl" level="1">{{ __('<ns>.index_title') }}</flux:heading>
      <flux:subheading size="lg" class="mb-6">{{ __('<ns>.index_subheading') }}</flux:subheading>
    </div>

    <flux:button as="a" href="{{ route('<ns>.create') }}" variant="primary" icon="plus">
      {{ __('<ns>.new_entity') }}
    </flux:button>
  </div>
  <flux:separator variant="subtle" />
</div>
```
- CTA principal siempre a la **derecha**, `variant="primary"` + `icon="plus"`.
- Nivel semántico accesible: `heading level="1"`.

---

## 3) Filtros (obligatorio)

**Principios**
- Siempre incluir al menos **un filtro útil**.
- **Inputs de texto**: `wire:model.live.debounce.250ms`.
- **Selects**: `wire:model.live`.
- **Reset automático de página** al modificar filtros (`updated()` o `updating()`).

```blade
<div class="flex flex-wrap gap-4 w-full items-end">
  <div class="max-w-[260px] flex-1">
    <flux:input size="sm" class="w-full" wire:model.live.debounce.250ms="q"
      :label="__('common.search')" placeholder="{{ __('<ns>.search_placeholder') }}" />
  </div>

  <div class="min-w-[160px]">
    <flux:select size="sm" wire:model.live="status" :label="__('common.status')">
      <option value="">{{ __('common.all') }}</option>
      <option value="1">{{ __('common.active') }}</option>
      <option value="0">{{ __('common.inactive') }}</option>
    </flux:select>
  </div>

  <div>
    <flux:button size="sm" variant="ghost" wire:click="resetFilters">
      {{ __('common.clear') }}
    </flux:button>
  </div>
</div>
```

> Todos los controles usan `size="sm"`. Evitar botones `primary` en filtros.

---

## 4) Barra de acciones contextual (opcional)
Cuando existan acciones masivas (ej. eliminar varios registros o exportar):
```blade
<div class="flex justify-end gap-2 items-center text-sm opacity-80">
  <flux:button size="sm" variant="ghost">{{ __('common.export') }}</flux:button>
  <flux:button size="sm" variant="ghost">{{ __('common.delete_selected') }}</flux:button>
</div>
<flux:separator variant="subtle" class="my-2" />
```

---

## 5) Tabla (estructura, colores, dark mode)

**Contenedor y bordes**
```blade
<div class="overflow-hidden border border-gray-200 dark:border-neutral-700 rounded-lg">
  <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
    <!-- thead / tbody -->
  </table>
</div>
```
- **Sin sombras ni bordes dobles**. Minimalismo limpio.
- **Textos**: `text-gray-800 dark:text-neutral-200`.

**Encabezados (`thead`)**
```blade
<th wire:click="sort('column')" class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
  <span class="inline-flex items-center gap-1">{{ __('<ns>.col') }}
    @if ($sortBy === 'column') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
  </span>
</th>
```

**Celdas (`tbody`)**
```blade
<td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">…</td>
```

**Estado vacío**
```blade
<tr>
  <td colspan="100" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
    {{ __('common.empty_state') }}
  </td>
</tr>
```

**Indicador de carga opcional**
```blade
<div wire:loading.flex class="absolute inset-0 items-center justify-center bg-white/50 dark:bg-black/25">
  <flux:spinner size="lg" />
</div>
```

---

## 6) Columna de identidad (imagen + texto)

```blade
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
```

---

## 7) Badges de estado

```blade
@php
  $state = $item->status;
  $styles = [
    'active' => 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-950/40 dark:text-green-300 dark:ring-green-900',
    'paused' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-900',
    'inactive' => 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800',
    'prospect' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-900',
  ];
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $styles[$state] ?? '' }}">
  {{ __('<ns>.status.' . $state) }}
</span>
```

> En dark mode, evitar colores saturados; usar opacidades `950/40`.

---

## 8) Acciones por fila

```blade
<td class="align-top px-6 py-4 text-end text-sm font-medium">
  <span class="inline-flex items-center gap-2 space-x-1 text-xs text-gray-400 dark:text-neutral-500 whitespace-nowrap">
    <flux:button size="sm" as="a" wire:navigate href="{{ route('<ns>.edit', $item->uuid) }}">{{ __('common.edit') }}</flux:button>

    <flux:modal.trigger name="confirm-delete-{{ $item->uuid }}">
      <flux:button size="sm" variant="ghost" wire:click="confirmDelete('{{ $item->uuid }}')">
        {{ __('common.delete') }}
      </flux:button>
    </flux:modal.trigger>
  </span>
</td>
```
- **Eliminar**: `variant="ghost"` en la tabla.
- **Eliminar**: Debe usar trigger siempre para modal confirmación
- Debe reutilizar el mismo modal para todos los registros, asignando el id/uuid a eliminar por livewire.
- Dentro del modal, el botón confirmatorio usa `variant="danger"`.

**Modal estándar**
```blade
<flux:modal name="confirm-delete-{{ $item->uuid }}" class="min-w-[22rem]" x-data
  @<ns>-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-{{ $item->uuid }}' })">
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
      <flux:button variant="danger" wire:click="delete">{{ __('common.confirm_delete') }}</flux:button>
    </div>
  </div>
</flux:modal>
```

---

## 9) Orden manual (columna de reorden)

```blade
<td class="align-top px-3 py-3 text-sm w-8">
  <div class="flex flex-col items-center leading-none">
    <a wire:click.prevent="moveUp({{ $item->id }})" title="{{ __('common.move_up') }}" class="cursor-pointer text-gray-400 hover:text-gray-600 dark:text-neutral-500 dark:hover:text-neutral-300">
      <x-icons.lucide.chevron-up class="h-4 w-4" />
    </a>
    <a wire:click.prevent="moveDown({{ $item->id }})" title="{{ __('common.move_down') }}" class="cursor-pointer text-gray-400 hover:text-gray-600 dark:text-neutral-500 dark:hover:text-neutral-300">
      <x-icons.lucide.chevron-down class="h-4 w-4" />
    </a>
  </div>
</td>
```

> Desactivar sorting (`wire:click="sort()"`) cuando haya columna `order`.

---

## 10) Feedback y eventos Livewire

- Usar `$this->dispatch('<entity>-deleted')`, `$this->dispatch('<entity>-saved')`, etc.
- Abrir modales usando trigger de flux
- Cerrar modales escuchando el evento:  
  `@<entity>-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-<entity>' })"`.
- Mantiene la UX fluida y sin recargas.

---

## 11) Accesibilidad e i18n

- `heading level="1"` en el header.
- Iconos accesibles con `aria-label` si no hay texto visible.
- Textos, estados, placeholders y acciones siempre traducidos (`__()`).

---

## 12) Checklist de revisión

- [ ] Header con `heading/subheading` y CTA `primary` a la derecha.
- [ ] Filtros: input con `debounce.250ms`, selects `live`, botón **Limpiar** `ghost`.
- [ ] Si aplica, barra contextual con acciones masivas.
- [ ] Tabla sin sombras ni bordes dobles.
- [ ] Identidad: avatar/iniciales + datos agrupados.
- [ ] Badges traducidos con contraste adecuado.
- [ ] Botones `size="sm"`; destructivos `ghost`; confirmaciones `danger`.
- [ ] Eventos Livewire (`*-deleted`) usados para feedback.
- [ ] Orden manual: columna con flechas; sin sort duplicado.
- [ ] i18n consistente (`common.*`, `<ns>.*`).

---

> **Uso:** Esta versión 2.0 consolida el patrón visual y de código para todos los listados FitTrack, garantizando coherencia, accesibilidad y mantenibilidad.

