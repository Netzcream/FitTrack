
# Guía de Formularios - Estándar Unificado Luniqo · v3.0

## 0) Principios generales
- Formularios alineados a la izquierda, ancho máximo `max-w-3xl`.
- Header sticky obligatorio.
- Footer compacto al final del contenido.
- Flux obligatorio en todos los campos.
- Sin `@error` ni `<label>` adicionales.# Guía de Formularios Simples - Estándar Unificado (Luniqo) · v2.2

**Alcance:** Formularios con pocos campos y baja lógica (alta frecuencia): creación/edición básica, asignaciones rápidas, cambios puntuales. Basado en Livewire + Flux + Tailwind.  
**Fecha:** 2025-10-13  
**Cambios clave:** manejo de imágenes con Spatie + Livewire, reglas de íconos, subtítulos, redirección post-create, ocultar campos de orden, y correcciones de UI (sin spinners salvo casos de carga de imagen, evitar text-sm, evitar @error y <label> cuando se usa Flux).

---

## 0) Reglas nuevas y errores frecuentes

- No agregar **spinners** en formularios salvo el overlay de carga sobre la imagen (avatar/cover/galería) cuando se está subiendo vía Livewire.
- Subtítulo corto (1 línea ideal, 2 máx.) para evitar solaparse con `<guardado>`, `<volver al listado>` y botones.

### Subida de imágenes

- Siempre con **Spatie Media Library** y **Livewire**.
- Mostrar **preview**; permitir eliminar archivo temporal y el ya subido.
- Para múltiples imágenes, usar **array separado** del `wire:model` del input y **límite máximo** (por defecto, 6).
- Ocultar `<input type="file">`; usar **placeholder clickeable** + botón “Subir imagen” a la derecha.
- Overlay con spinner solo mientras `wire:loading` la imagen objetivo.

### Redirección post-create
Si el formulario es de creación y no está tildado “Volver al listado”, redirigir a la misma vista pero en modo edición de la entidad recién creada.

### Campos y estilos

- Campos de **orden (order)** no se muestran en formulario; se gestionan desde el Index.
- No usar `text-sm` en textos base del formulario. Dejar tamaños por defecto de Tailwind o los que provee Flux.
- No usar `<flux:label>` ni `<label>` cuando ya se usa `<flux:input>` / `<flux:select>`: esos componentes traen `:label="…"`.  
- No usar `@error` para campos rendereados con componentes Flux: estos ya incluyen visualización de error.
- Cuando uses `<flux:input>` / `<flux:select>`, envolvé cada uno en un `<div>` para evitar quiebres de layout con estados de error incluidos por Flux.

### Íconos

- Fuera de Flux: `<x-icons.lucide.nombreicono />` con `php artisan icons:lucide <nombre-icono>`.
- Dentro de componentes Flux (prop `icon="…"`) recomendamos descargarlo también con: `php artisan flux:icon <nombre-icono>`.

---

## 1) Anatomía y jerarquía visual

1. **Header sticky:** título, subtítulo (corto), acciones (checkbox “Volver al listado”, Volver, Guardar).  
2. **Separador superior:** `flux:separator` a ancho completo.  
3. **Contenido:** grilla, validaciones discretas (nativas de Flux).  
4. **Barra inferior compacta (no sticky)** con mismas acciones.  
5. **Separador inferior:** `flux:separator` para cierre visual.  

> El header sticky garantiza acceso constante a acciones críticas.

---

## 2) Contenedores y clases base

**Wrapper general:**  
`div.flex.items-start.max-md:flex-col`

**Columna principal:**  
`div.flex-1.self-stretch.w-full.max-md:pt-6`

**Formulario:**  
`<form class="space-y-6" wire:submit.prevent="save">`

**Contenido:**  
`div.max-w-3xl.space-y-4.pt-2`

> Ancho consistente (`max-w-3xl`) para header, contenido y barra inferior.

---

## 3) Header sticky

Estructura base:

```html
<div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
  <div class="flex items-center justify-between gap-4 max-w-3xl">
    <div>
      <flux:heading size="xl" level="1">{{ $editMode ? __('entity.edit_title') : __('entity.new_title') }}</flux:heading>
      <flux:subheading size="lg" class="mb-6">{{ $editMode ? __('entity.edit_subheading') : __('entity.new_subheading') }}</flux:subheading>
    </div>
    <div class="flex items-center gap-3">
      <flux:checkbox size="sm" label="{{ __('site.back_list') }}" wire:model.live="back" />
      <flux:button as="a" variant="ghost" href="{{ route('<…index>') }}" size="sm">{{ __('site.back') }}</flux:button>
      <flux:button type="submit" size="sm">{{ $editMode ? __('entity.update_button') : __('entity.create_button') }}</flux:button>
    </div>
  </div>
  <flux:separator variant="subtle" class="mt-2" />
</div>
```

---

## 4) Acciones y footer

- Repetir las acciones (checkbox, volver, guardar) en la **barra inferior compacta** (`opacity-80`).  
- No sticky, para cierre natural del flujo visual.  

```html
<div class="pt-6 max-w-3xl">
  <div class="flex justify-end gap-3 items-center text-sm opacity-80">
    <flux:checkbox size="sm" label="{{ __('site.back_list') }}" wire:model.live="back" />
    <flux:button as="a" variant="ghost" href="{{ route('<…index>') }}" size="sm">{{ __('site.back') }}</flux:button>
    <flux:button type="submit" size="sm">{{ $editMode ? __('entity.update_button') : __('entity.create_button') }}</flux:button>
  </div>
</div>
```

---

## 5) Buenas prácticas Livewire

```php
$validated = $this->validate();
$this->dispatch('saved');

if ($this->back) {
    return redirect()->route('<…index>');
}

session()->flash('success', $this->editMode ? __('entity.updated') : __('entity.created'));
```
- Usar `wire:model.defer` en inputs para evitar renders excesivos.
- Cargar listas/fks en `mount()`, no en `render()`.

---

## 6) Checklist rápido

- [x] Header sticky con blur.  
- [x] Separadores superior e inferior sutiles.  
- [x] Ancho consistente `max-w-3xl`.  
- [x] Inputs dentro de `div` individuales.  
- [x] Sin `@error` ni `<label>` redundantes.  
- [x] Spinners solo en subida de imagen.  
- [x] Redirect post-create coherente.  
- [x] Íconos Lucide o Flux.  
- [x] `text-base` por defecto (no `text-sm`).  
- [x] `flux:button` y `flux:checkbox` tamaño `sm`.  

---

- Sin `text-sm` en texto general.
- Sin spinners salvo en uploads.
- Spatie Media Library obligatoria para imágenes.
- Validaciones nativas de Flux.
- Todos los campos envueltos en `<div>`.

---

## 1) Anatomía base

### 1.1 Estructura general
```
<div class="flex items-start max-md:flex-col">
  <div class="flex-1 self-stretch w-full max-md:pt-6">
    <!-- Header -->
    <!-- Body -->
    <!-- Footer -->
  </div>
</div>
```

### 1.2 Header sticky
```
<div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
  <div class="flex items-center justify-between gap-4 max-w-3xl">
    <div>
      <flux:heading size="xl" level="1">{{ $title }}</flux:heading>
      <flux:subheading size="lg" class="mb-6">{{ $subtitle }}</flux:subheading>
    </div>
    <div class="flex items-center gap-3">
      <flux:checkbox size="sm" wire:model.live="back" :label="__('site.back_list')" />
      <flux:button as="a" href="{{ $routeBack }}" variant="ghost" size="sm">{{ __('site.back') }}</flux:button>
      <flux:button type="submit" size="sm">{{ $buttonLabel }}</flux:button>
    </div>
  </div>
  <flux:separator variant="subtle" class="mt-2" />
</div>
```

---

## 2) Contenedor del formulario
```
<form wire:submit.prevent="save" class="space-y-6">
  <div class="max-w-3xl space-y-4 pt-2">
```

---

## 3) Campos estándar

```
<div>
  <flux:input wire:model.defer="name" :label="__('entity.name')" />
</div>

<div>
  <flux:select wire:model.defer="status" :label="__('entity.status')">
    <option value="active">Activo</option>
    <option value="inactive">Inactivo</option>
  </flux:select>
</div>

<div>
  <flux:textarea wire:model.defer="description" rows="6" :label="__('entity.description')" />
</div>
```

### Reglas:
- `wire:model.defer` por defecto.
- Campos dentro de `<div>` siempre.
- Orden recomendado: generales → específicos → secundarios.

---

## 4) Imágenes (Spatie + Livewire)

### 4.1 Reglas obligatorias
- Input file oculto.
- Preview visible.
- Botón “Subir imagen”.
- Spinner en overlay:
```
<div wire:loading wire:target="image" class="absolute inset-0 bg-black/20"></div>
```

### 4.2 Ejemplo completo
```
<div class="space-y-2">
  <div class="relative w-40 h-40 border rounded-lg overflow-hidden">
    @if ($tempImage)
      <img src="{{ $tempImage->temporaryUrl() }}" class="object-cover w-full h-full" />
    @elseif ($entity?->hasMedia('cover'))
      <img src="{{ $entity->getFirstMediaUrl('cover') }}" class="object-cover w-full h-full" />
    @endif

    <div wire:loading wire:target="tempImage"
         class="absolute inset-0 bg-black/10 flex items-center justify-center">
      <x-icons.lucide.loader class="w-6 h-6 animate-spin" />
    </div>
  </div>

  <flux:button size="sm" type="button" @click="$refs.input.click()">Subir imagen</flux:button>
  <input type="file" x-ref="input" class="hidden" wire:model="tempImage" accept="image/*">
</div>
```

---

## 5) Footer compacto
```
<div class="pt-6 max-w-3xl">
  <div class="flex justify-end gap-3 items-center text-sm opacity-80">
    <flux:checkbox size="sm" wire:model.live="back" :label="__('site.back_list')" />
    <flux:button as="a" href="{{ $routeBack }}" variant="ghost" size="sm">{{ __('site.back') }}</flux:button>
    <flux:button type="submit" size="sm">{{ $buttonLabel }}</flux:button>
  </div>
</div>
```

---

## 6) Excepciones: definiciones completas

### 6.1 Timeline

**Cuándo usarlo:**  
- Tickets, procesos con historial, aprobaciones, comentarios.

**Estructura visual:**  
- Siempre debajo del formulario principal.  
- Caja contenedora con:
```
bg-gray-50 dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700 p-4
```

**Reglas:**  
- Eventos y comentarios ordenados por fecha ascendente.  
- Evento: ícono a la izquierda, texto a la derecha.  
- Comentario interno: borde izquierdo amarillo `border-l-4 border-l-yellow-500`.

**Ejemplo:**
```
<div class="space-y-4">
  @foreach ($timeline as $item)
    @if ($item->type === 'comment')
      <div class="p-4 rounded-lg border border-gray-200 dark:border-neutral-700 {{ $item->internal ? 'border-l-4 border-l-yellow-500' : '' }}">
        <div class="text-sm">{{ $item->message }}</div>
      </div>
    @else
      <div class="flex items-start gap-3 text-sm opacity-80">
        <x-icons.lucide.activity class="w-4 h-4" />
        <div>{{ $item->description }}</div>
      </div>
    @endif
  @endforeach
</div>
```

---

### 6.2 Timeline + comentario nuevo
- Caja independiente al final.
- Textarea + checkbox “interno”.
- Botón “Agregar comentario”.

```
<div class="bg-gray-50 dark:bg-neutral-800 border rounded-lg p-4">
  <flux:textarea wire:model.defer="newComment" rows="3" :label="__('add_comment')" />
  <div class="flex justify-between mt-2">
    <flux:checkbox size="sm" wire:model.defer="internal" :label="__('internal_comment')" />
    <flux:button size="sm" wire:click="addComment">Agregar</flux:button>
  </div>
</div>
```

---

### 6.3 Sidebar en modo edición

**Cuándo usarlo:**  
- Inventario, Tickets, Productos, Healthcare.

**Reglas:**  
- Nunca en modo creación.
- Contenido típico:
  - Estados
  - Metadata
  - Estadísticas
  - Imagen del item

**Estructura:**
```
<div class="w-full md:w-80 md:pl-6 md:border-l md:border-gray-200 dark:md:border-neutral-700 space-y-4">
  <!-- contenido -->
</div>
```

---

### 6.4 Tabs / Multi-step

**Cuándo usarlo:**  
- Formularios largos (Sorteos, Productos complejos, Procesos).

**Reglas:**  
- Flux tabs si están disponibles; si no, Preline Tabs.  
- Validación por paso.  
- No mezclar campos de diferentes secciones.

**Ejemplo básico con pasos:**
```
<div class="flex gap-4 border-b pb-2">
  <button wire:click="$set('step', 'general')" class="{{ $step === 'general' ? 'font-bold' : '' }}">General</button>
  <button wire:click="$set('step', 'premios')">Premios</button>
</div>

@if ($step === 'general')
  …
@endif
```

---

### 6.5 Maestro–detalle (repeaters)

**Reglas:**  
- Cada línea debe tener `wire:key="row-{{ $index }}"`.
- Validación por índice.
- Botón agregar/eliminar.

**Ejemplo:**
```
@foreach ($items as $i => $row)
  <div wire:key="row-{{ $i }}" class="flex gap-3">
    <flux:input wire:model.defer="items.{{ $i }}.name" label="Nombre" />
    <flux:button variant="ghost" size="sm" wire:click="remove({{ $i }})">X</flux:button>
  </div>
@endforeach

<flux:button size="sm" wire:click="add">Agregar ítem</flux:button>
```

---

## 7) Estilos y dark mode
- Bordes: `border-gray-200 dark:border-neutral-700`.
- Contenedores sin sombras.
- Textos neutros.
- Badges suaves en timelines.

---

## 8) Checklist final v3
- [ ] Header sticky ✓
- [ ] Footer compacto ✓
- [ ] Campos con `<div>` ✓
- [ ] Imágenes con overlay correcto ✓
- [ ] Timeline definido y aplicado ✓
- [ ] Sidebar definido ✓
- [ ] Tabs/multistep definidos ✓
- [ ] Maestro-detalle definido ✓
- [ ] max-w-3xl uniforme ✓

