# Guía de diseño de *Index* (listados) — Estándar Unificado

> **Objetivo**: documentar, de forma precisa y reusable, cómo debe verse y comportarse **cualquier** index de FitTrack (Students, Payments, Plans, etc.), tomando como referencia el de *Alumnos*. Este documento define estructura, clases, estados, filtros, ordenamiento, acciones, accesibilidad y buenas prácticas Livewire/Flux/Tailwind.

---

## 1) Anatomía del index

**Bloques principales (de arriba hacia abajo):**
1. **Header**: título, subtítulo y CTA principal (ej. “Nuevo …”).
2. **Separador sutil**.
3. **Bloque de filtros** (buscador + selects + reset) — *siempre presente*.
4. **Tabla**: `thead` (encabezados + orden), `tbody` (filas de datos), `tfoot` (opcional).
5. **Paginación** (cuando corresponda).
6. **Modales** (creación/confirmaciones) — *preferentemente montados al final del componente.*

**Contenedor general recomendado**
- Wrapper: `div.flex.items-start.max-md:flex-col`
- Columna principal: `div.flex-1.self-stretch.max-md:pt-6`
- Espaciados verticales: `space-y-6` en secciones principales.

---

## 2) Header

**Estructura**
```html
<div class="relative mb-6 w-full">
  <div class="flex items-center justify-between gap-4 flex-wrap">
    <div>
      <flux:heading size="xl" level="1">{{ __("<ns>.index_title") }}</flux:heading>
      <flux:subheading size="lg" class="mb-6">{{ __("<ns>.index_subheading") }}</flux:subheading>
    </div>

    <flux:modal.trigger name="create-entity">
      <flux:button variant="primary" icon="plus">
        {{ __("<ns>.new_entity") }}
      </flux:button>
    </flux:modal.trigger>
  </div>
  <flux:separator variant="subtle" />
</div>
```

**Notas**
- Mantener **CTA a la derecha**; usar `variant="primary"` e `icon="plus"` para creación.
- Título `h1` accesible con `level="1"`.
- `flux:separator` delimita header de filtros/tabla.

---

## 3) Filtros (obligatorio)

**Principios**
- Siempre incluir **al menos un filtro útil**. Si hay relaciones (plan, objetivo, tags), agregarlas.
- **Bindings**: `wire:model.live.debounce.250ms` para *inputs de texto*; `wire:model.live` para *selects*.
- Agregar botón **“Limpiar / Aplicar”** según patrón del recurso. Sugerencia: mantener un botón *ghost* “Aplicar filtros” y otro para **“Limpiar”** cuando corresponda.

**Estructura de filtros**
```html
<div class="flex flex-wrap gap-4 w-full items-end">
  <!-- Buscador -->
  <div class="max-w-[260px] flex-1">
    <flux:label class="text-xs">{{ __("common.search") }}</flux:label>
    <flux:input size="sm" class="w-full"
      wire:model.live.debounce.250ms="q"
      placeholder="{{ __("<ns>.search_placeholder") }}" />
  </div>

  <!-- Select de estado (ej.) -->
  <div class="min-w-[150px]">
    <flux:label class="text-xs">{{ __("common.status") }}</flux:label>
    <flux:select size="sm" wire:model.live="status">
      <option value="">{{ __("common.all") }}</option>
      <!-- …options… -->
    </flux:select>
  </div>

  <!-- Más selects/relaciones -->
  <!-- … -->

  <!-- Acciones de filtro -->
  <div class="flex items-end gap-2 ml-auto">
    <flux:button size="sm" variant="ghost" wire:click="applyFilters">{{ __("common.filter") }}</flux:button>
    <flux:button size="sm" variant="ghost" wire:click="resetFilters">{{ __("common.clear") }}</flux:button>
  </div>
</div>
```

**Claves de UX**
- **Tamaños** `size="sm"` en todos los controles.
- Etiquetas con `flux:label.text-xs`.
- Orden natural: texto → selects por importancia.

---

## 4) Tabla (estructura, colores y dark mode)

**Contenedor y bordeado**
```html
<div class="flex flex-col">
  <div class="-m-1.5 overflow-x-auto">
    <div class="p-1.5 min-w-full inline-block align-middle">
      <div class="overflow-hidden border border-gray-200 dark:border-neutral-700 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
          <!-- thead / tbody -->
        </table>
      </div>
    </div>
  </div>
</div>
```
- **No usar** bordes oscuros en modo claro ni viceversa. Seguir: `border-gray-200` (light) / `dark:border-neutral-700`.
- Líneas internas: `divide-gray-200` / `dark:divide-neutral-700`.

**Encabezados (`thead`) con orden**
```html
<thead>
  <tr>
    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left"
        wire:click="sort('column')">
      <span class="inline-flex items-center gap-1">{{ __("<ns>.col_label") }}
        @if ($sortBy === 'column') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
      </span>
    </th>
    <!-- …otras columnas… -->
    <th class="px-6 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-neutral-500">{{ __("common.actions") }}</th>
  </tr>
</thead>
```
- **Ordenamiento**: Habilitar solo si el modelo **no** tiene orden manual (ver §7).
- Alineación: texto/numérico según la semántica; acciones **text-end**.

**Celdas (`tbody`)**
```html
<td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">…</td>
```
- Tipografía primaria en `text-gray-800` / `dark:text-neutral-200`.
- Columnas numéricas o KPIs: usar `text-end`.

**Estados vacíos**
```html
<tr>
  <td colspan="100" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
    {{ __("common.empty_state") }}
  </td>
</tr>
```

---

## 5) Imágenes y columna de identidad

**Regla**: si hay imagen, mostrarla **primera** dentro de la celda de identidad.

**Variantes**
- **Círculo** (avatar): `h-8 w-8 rounded-full overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800` + `object-cover` en `<img>`.
- **Cuadrado con radio**: sustituir `rounded-full` por `rounded-lg` y ajustar tamaño (`h-10 w-10`).
- **Rectángulo horizontal** (cover): wrapper con `h-12 w-20 rounded-md overflow-hidden`.

**Agrupar datos** (nombre + email, título + categoría)
```html
<div class="inline-flex items-center gap-3">
  <!-- imagen -->
  <div class="h-8 w-8 rounded-full overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 flex items-center justify-center">
    <!-- <img …> o iniciales -->
  </div>
  <div class="leading-tight">
    <div class="font-medium text-gray-900 dark:text-neutral-100">{{ $item->name ?? '—' }}</div>
    <div class="text-xs text-gray-500 dark:text-neutral-400">{{ $item->email ?? '—' }}</div>
  </div>
</div>
```

---

## 6) Badges de estado (con traducción)

**Principio**: *texto traducido* mediante `__("<componente>.<estado>")` (ej. `__("products.new")`).

**Estilos sugeridos** (background + texto asegurando contraste):
```html
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
  {{ __("<ns>.status.".$state) }}
</span>
```

---

## 7) Ordenamiento de columnas vs. orden manual

- **Si el modelo expone orden manual** (campo `order` o similar):
  - **Deshabilitar** `wire:click="sort('…')"` en encabezados.
  - Agregar **columna de reorden** al inicio con flechas:
    ```html
    <td class="align-top px-3 py-3 text-sm w-12">
      <div class="flex flex-col items-center gap-1">
        <flux:button size="sm" variant="ghost" icon="chevron-up" wire:click="moveUp({{ $item->id }})" />
        <flux:button size="sm" variant="ghost" icon="chevron-down" wire:click="moveDown({{ $item->id }})" />
      </div>
    </td>
    ```
  - Implementar en el backend `moveUp/moveDown` con *swap* seguro y `resetPage()` tras el cambio.

- **Si NO hay orden manual**: habilitar orden por columnas **whitelist** en el componente Livewire (`$allowed`).

---

## 8) Botones y acciones

**Reglas globales**
- **Siempre** `<flux:button size="sm" />`.
- El botón **más crítico/destructivo** usa `variant="ghost"` (p.ej. “Eliminar”).
- **Espaciado** entre botones al final de la celda: `space-x-1` (usar wrapper).
- **Confirmaciones**: cuando se requiera, usar `flux:modal.trigger` + `flux:modal`.
- Si se necesita un **link**, hacerlo **como button**: `as="a" href="…"`.

**Patrón de acciones por fila**
```html
<td class="align-top px-6 py-4 text-end text-sm font-medium">
  <span class="inline-flex items-center gap-2 space-x-1 text-xs text-gray-400 dark:text-neutral-500 whitespace-nowrap">
    <flux:button size="sm" as="a" href="{{ route('…') }}">{{ __("common.view") }}</flux:button>
    <flux:button size="sm" as="a" wire:navigate href="{{ route('…edit', $item) }}">{{ __("common.edit") }}</flux:button>

    <flux:modal.trigger name="confirm-delete-{{ $item->id }}">
      <flux:button size="sm" variant="ghost">{{ __("common.delete") }}</flux:button>
    </flux:modal.trigger>
  </span>
</td>
```

**Modal de confirmación estandarizado**
```html
<flux:modal name="confirm-delete-{{ $item->id }}" class="min-w-[22rem]">
  <div class="space-y-6">
    <div>
      <flux:heading size="lg">{{ __("common.delete_title") }}</flux:heading>
      <flux:text class="mt-2">{{ __("common.delete_msg") }}</flux:text>
    </div>
    <div class="flex gap-2">
      <flux:spacer />
      <flux:modal.close><flux:button variant="ghost">{{ __("common.cancel") }}</flux:button></flux:modal.close>
      <flux:button variant="danger" wire:click="delete({{ $item->id }})">{{ __("common.confirm_delete") }}</flux:button>
    </div>
  </div>
</flux:modal>
```

---

## 9) Livewire: helpers y convenciones

- `public string $sortBy = 'id'; public string $sortDirection = 'asc';`
- Método `sort(string $column)` con **lista blanca** de columnas ordenables.
- Búsqueda: actualizar página en `updatedQ()` y `updated<Filter>()` → `resetPage()`.
- Paginación: `use WithPagination; public int $perPage = 10;`
- Filtros: `applyFilters()` → `resetPage()`; `resetFilters()` → setear `null`/`''`.

---

## 10) Accesibilidad e i18n

- Iconografía con texto visible o `aria-label` en botones de solo ícono.
- Encabezado con `level="1"`.
- **Traducciones**: todas las etiquetas, placeholders, estados y acciones deben estar en archivos `lang` (ej. `__('students.active')`).
- Usar `ucfirst()` solo cuando el texto no provenga de `__()` y no sea clave i18n.

---

## 11) Rendimiento

- Cargar relaciones **necesarias** con `with([...])` para evitar N+1.
- Para imágenes, intentar conversiones (`thumb`) de Spatie; fallback a original.
- `loading="lazy"` en `<img>`.

---

## 12) Tabla canónica: **SIEMPRE** usar `<x-data-table>`

**Norma:** para listados tabulares en FitTrack, **no** se arma la tabla manualmente. Usar el componente `<x-data-table>` con sus *slots* (`filters`, `head`, `modal`) y la paginación provista.

**Props y slots clave**
- **Prop** `:pagination` → colección paginada (`LengthAwarePaginator`).
- **Slots**:
  - `filters` (filtros arriba)
  - `head` (encabezados con orden, si aplica)
  - `modal` (confirmaciones/diálogos)
- **Contenido de filas**: va en el `slot` principal.

**Ejemplo preferido (correcto)**
```blade
<section class="w-full">
  <x-data-table :pagination="$users">
    <x-slot name="filters">
      <div class="flex flex-wrap gap-4 w-full items-end">
        <div class="max-w-[260px] flex-1">
          <flux:label class="text-xs">{{ __('common.search') }}</flux:label>
          <flux:input wire:model.live.debounce.250ms="search" size="sm" class="w-full"
                      placeholder="{{ __('users.search_placeholder') }}" />
        </div>

        <div class="min-w-[180px]">
          <flux:label class="text-xs">{{ __('users.role') }}</flux:label>
          <flux:select wire:model.live="role" size="sm" class="w-full">
            <option value="">{{ __('common.all') }}</option>
            @foreach ($roles as $r)
              <option value="{{ $r }}">{{ ucfirst($r) }}</option>
            @endforeach
          </flux:select>
        </div>

        <div class="flex items-end gap-2 ml-auto">
          <flux:button size="sm" variant="ghost" wire:click="resetFilters">
            {{ __('common.clear') }}
          </flux:button>
        </div>
      </div>
    </x-slot>

    <x-slot name="head">
      <th wire:click="sort('name')"
          class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
        <span class="inline-flex items-center gap-1">
          {{ __('users.name') }}
          @if ($sortBy === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
        </span>
      </th>

      <th wire:click="sort('email')"
          class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
        {{ __('users.email') }}
      </th>

      <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
        {{ __('users.role') }}
      </th>

      <th wire:click="sort('created_at')"
          class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
        {{ __('users.created_at') }}
      </th>

      <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
        {{ __('common.actions') }}
      </th>
    </x-slot>

    @forelse ($users as $user)
      <tr class="divide-y divide-gray-200 dark:divide-neutral-700">
        <td class="align-top px-6 py-4 text-sm font-medium text-gray-800 dark:text-neutral-200">
          {{ $user->name }}
        </td>

        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
          {{ $user->email }}
        </td>

        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
          @foreach ($user->roles as $role)
            <span
              class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium
                     bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200
                     dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-900 mr-1">
              {{ ucfirst($role->name) }}
            </span>
          @endforeach
        </td>

        <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
          {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '—' }}
        </td>

        <td class="align-top px-6 py-4 text-end text-sm font-medium">
          <span class="inline-flex items-center gap-2 space-x-1 text-xs text-gray-400 dark:text-neutral-500 whitespace-nowrap">
            <flux:button size="sm" as="a" wire:navigate
                         href="{{ route('tenant.dashboard.users.edit', $user->id) }}">
              {{ __('common.edit') }}
            </flux:button>

            @if (auth()->user()->id !== $user->id)
              <flux:modal.trigger name="confirm-delete-user">
                <flux:button size="sm" variant="ghost"
                             wire:click="confirmDelete('{{ $user->id }}')">
                  {{ __('common.delete') }}
                </flux:button>
              </flux:modal.trigger>
            @endif
          </span>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="100"
            class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
          {{ __('common.empty_state') }}
        </td>
      </tr>
    @endforelse

    <x-slot name="modal">
      <flux:modal name="confirm-delete-user" class="min-w-[22rem]" x-data
                  @user-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-user' })">
        <div class="space-y-6">
          <div>
            <flux:heading size="lg">{{ __('users.confirm_delete_title') }}</flux:heading>
            <flux:text class="mt-2">{{ __('users.confirm_delete_msg') }}</flux:text>
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
  </x-data-table>
</section>
```

> Reutilizar siempre que el recurso sea tabular. Si la vista exige tarjetas, adaptar manteniendo los **filtros** y **acciones** con las mismas reglas.

---

## 13) Ejemplo mínimo aplicable a cualquier recurso

```blade
<section class="w-full">
  <x-data-table :pagination="$items">
    <x-slot name="filters">
      <!-- (ver §3) -->
    </x-slot>

    <x-slot name="head">
      <!-- (ver §4) encabezados con orden si procede -->
    </x-slot>

    @forelse ($items as $item)
      <tr>
        <!-- Col. identidad con imagen (ver §5) -->
        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
          <!-- … -->
        </td>
        <!-- Col. estado como badge (ver §6) -->
        <td class="align-top px-6 py-4 text-sm">
          <!-- … -->
        </td>
        <!-- Más columnas … -->
        <td class="align-top px-6 py-4 text-end text-sm font-medium">
          <!-- Acciones (ver §8) -->
        </td>
      </tr>
    @empty
      <!-- Estado vacío (ver §4) -->
    @endforelse

    <x-slot name="modal">
      <!-- Modales (creación / confirmación) (ver §8) -->
    </x-slot>
  </x-data-table>
</section>
```

---

## 14) Checklist de revisión (QA visual y funcional)

- [ ] Header con `heading/subheading`, CTA `primary` a la derecha.
- [ ] Filtros: input `debounce.250ms` + selects `live`; botón **Aplicar** y **Limpiar**.
- [ ] Tabla con bordes/`divide` compatibles light/dark.
- [ ] Identidad: imagen en primer lugar; agrupación nombre+mail/título+categoría.
- [ ] Estados como **badges** (bg + texto con contraste) **traducidos**.
- [ ] Botones `size="sm"`; destructivo `variant="ghost"`; espaciado final `space-x-1`.
- [ ] Confirmaciones con `flux:modal.trigger` + `flux:modal`.
- [ ] Ordenamiento por columnas **solo** si no existe orden manual.
- [ ] Si hay `order`: columna con flechas (up/down) y sin sorting en `thead`.
- [ ] Paginación visible (si aplica).
- [ ] Accesibilidad: `aria-label` en icon-only; `h1` correcto.
- [ ] Textos/estados/labels con `__()`.

---

## 15) Errores comunes y cómo evitarlos

- **Colores incorrectos** en dark mode → usar las clases de esta guía (neutral/grays equilibrados).
- **Ghost en primarios** → `ghost` solo para destructivos o secundarios críticos (ej. eliminar/archivar).
- **Links sueltos** → siempre `as="a"` dentro de `<flux:button>`.
- **Orden habilitado con `order`** → desactivar sorting en encabezados cuando haya reorden manual.
- **Filtros sin `resetPage()`** → paginación inconsistente al filtrar/buscar.

---

## 16) Nombres de claves i18n sugeridos

- `common.search`, `common.search_placeholder`
- `common.status`, `common.all`, `common.actions`
- `common.filter`, `common.clear`
- `common.view`, `common.edit`, `common.delete`, `common.delete_title`, `common.delete_msg`, `common.confirm_delete`, `common.cancel`
- `<ns>.index_title`, `<ns>.index_subheading`, `<ns>.status.<state>`

> Donde `<ns>` es el *namespace* del módulo (ej. `students`, `payments`).

---

### Apéndice A — Mapa rápido de clases Tailwind/Flux
- **Bordes**: `border-gray-200 dark:border-neutral-700`
- **Divide**: `divide-gray-200 dark:divide-neutral-700`
- **Texto primario**: `text-gray-900 dark:text-neutral-100`
- **Texto secundario**: `text-gray-500 dark:text-neutral-400`
- **Celda**: `px-6 py-4 text-sm`
- **Encabezado**: `px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500`
- **Avatar**: `h-8 w-8 rounded-full overflow-hidden border bg-gray-50 dark:bg-neutral-800`
- **Acciones**: wrapper `inline-flex items-center gap-2 space-x-1 text-xs`

---

> **Uso**: Copiar y adaptar. Mantener los patrones; solo variar labels, columnas y relaciones. Esta guía es la *single source of truth* para la UI de listados en FitTrack.

