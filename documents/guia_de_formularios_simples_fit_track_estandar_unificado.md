# Guía de **Formularios Simples** — Estándar Unificado (FitTrack)

> **Alcance**: Esta guía aplica a **formularios con pocos campos y baja lógica** (alta frecuencia de uso): creación/edición básica de entidades, cambios de credenciales, asignaciones rápidas, etc. Se basa en el patrón visual descrito y el ejemplo de *Usuarios* (Livewire + Flux + Tailwind), cuidando colores, tamaños, acciones y dark mode.

---

## 1) Anatomía y jerarquía visual

Bloques principales (de arriba hacia abajo):
1. **Header sticky** (fijo): título, subtítulo, acciones principales (Volver, Guardar, “Volver al listado”).
2. **Separador superior**: `flux:separator` **full width**.
3. **Contenido del formulario**: campos en grilla, validaciones discretas.
4. **Barra inferior compacta** (no sticky): repetición de acciones, bajo peso visual.
5. **Separador inferior**: `flux:separator` **full width** para cierre visual.

> **Principio**: el usuario **nunca pierde** acceso a las acciones críticas gracias al header sticky.

---

## 2) Contenedores y clases base

**Wrapper general**
- `div.flex.items-start.max-md:flex-col`
- Columna principal: `div.flex-1.self-stretch.w-full.max-md:pt-6`

**Formulario**
- `<form class="space-y-6" wire:submit.prevent="save">`
- Bloque de contenido: `div.max-w-3xl.space-y-4.pt-2`
- **Ancho máximo**: `max-w-3xl` para header, contenido y barra inferior (alineación consistente).

**Header sticky**
```html
<div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
  <div class="flex items-center justify-between gap-4 max-w-3xl">
    <!-- Título / Subtítulo -->
    <!-- Acciones (checkbox volver, volver, guardar) -->
  </div>
  <flux:separator variant="subtle" class="mt-2" />
</div>
```
- `bg-inherit` + `backdrop-blur` → coherencia de tema y **leve blur** al hacer scroll.
- `z-30` asegura superposición sobre el contenido.

**Barra inferior compacta**
```html
<div class="pt-6 max-w-3xl">
  <div class="flex justify-end gap-3 items-center text-sm opacity-80">
    <!-- checkbox volver / volver / guardar -->
  </div>
</div>
```
- **No sticky**: cierre natural del flujo, **sin** separadores extra.
- `opacity-80`: no compite con el header.

**Separadores**
- Superior (en header) e **inferior** al final del formulario: `flux:separator variant="subtle"`.

---

## 3) Tipografía, colores y dark mode

- Títulos: `<flux:heading size="xl" level="1">`
- Subtítulo: `<flux:subheading size="lg" class="mb-6">`
- Texto primario de campos: `text-gray-800 dark:text-neutral-200`
- Mensajes secundarios/ayuda: `text-gray-500 dark:text-neutral-400`
- **Dark mode**: evitar grises muy oscuros en bordes; preferir `neutral-*` en vez de `gray-*` cuando corresponda.

---

## 4) Campos y grillas

**Distribución**
- Grilla por defecto: `grid grid-cols-1 md:grid-cols-2 gap-6`.
- Espaciado vertical entre bloques: `space-y-4` en el contenedor.

**Inputs**
- Usar `<flux:input>` con `wire:model.defer`.
- `label` siempre presente; cuando se use `<flux:label>` fuera del input, tamaño `text-xs`.
- `autocomplete`: desactivado en lo sensible (p. ej., `autocomplete="off"` en texto; `new-password` en contraseñas).

**Radios/checkboxes**
```html
<input type="radio" class="form-radio accent-blue-600 dark:accent-blue-400 rounded focus:ring-2 focus:ring-blue-500">
```
- **Accent** azul consistente; `focus:ring-2` para accesibilidad.

**Validaciones**
```blade
@error('field')
  <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
@enderror
```
- Mensajes **discretos**: `text-xs` + `text-red-500`.

---

## 5) Acciones y botones

**Reglas generales**
- **Tamaño**: siempre `size="sm"`.
- **Primario**: *Guardar* (por defecto sin `variant`, o `variant="primary"` si se requiere resaltar).
- **Secundario destructivo/menor**: *Volver* con `variant="ghost"`.
- **Checkbox** “Volver al listado” se repite **arriba** y **abajo**.
- Si se requiere navegación: usar `as="a" href="…"` **dentro** del `<flux:button>`.

**Patrón de acciones (header y barra inferior)**
```html
<flux:checkbox size="sm" label="{{ __('site.back_list') }}" wire:model.live="back" />

<flux:button as="a" variant="ghost" href="{{ route('<…index>') }}" size="sm">
  {{ __('site.back') }}
</flux:button>

<flux:button type="submit" size="sm">
  {{ $editMode ? __('entity.update_button') : __('entity.create_button') }}
</flux:button>
```

**Confirmaciones**
- Para formularios simples no se presentan modales, salvo acciones críticas (p. ej., *Eliminar*); en ese caso usar patrón de `flux:modal.trigger` + `flux:modal` del estándar de Index.

---

### 5.1) Mensaje de acción (feedback) — `x-tenant.action-message`

- **Ubicación**: siempre **antes** del checkbox “Volver al listado”, tanto en el **header sticky** como en la **barra inferior**.
- **Evento**: `on="saved"` (se dispara con `$this->dispatch('saved')`).
- **Accesibilidad**: provee feedback inmediato sin bloquear el flujo.

**Snippet recomendado (header y footer):**
```blade
<x-tenant.action-message on="saved">
  {{ __('site.saved') }}
</x-tenant.action-message>
```

> Si no se requiere mostrar el mensaje en alguna vista específica, puede omitirse, pero por defecto **se recomienda incluirlo** para formularios simples.

## 6) Livewire — convenciones mínimas

**Props comunes**
```php
public bool $editMode = false;   // Modo edición vs. creación
public bool $back = false;       // Checkbox “Volver al listado”
```

**Mount**
- Cargar datos iniciales y setear `$editMode` según exista entidad.
- Poblar colecciones para radios/selects (ordenadas alfabéticamente).

**Validación**
- Reglas breves y claras. Si hay campos condicionales (p. ej., contraseña en edición), aplicar reglas **solo** si el campo se envía o si no es edición.

**Save**
```php
$validated = $this->validate($rules);
// create/update + fill
$this->dispatch('saved'); // feedback Livewire (toast/snackbar según layout)
if ($this->back) return redirect()->route('<…index>');
session()->flash('success', $this->editMode ? __('entity.updated') : __('entity.created'));
```
- **Redirección** solo si `$back === true`.
- **Flash** de éxito para la siguiente request cuando se permanece en la vista.

**UX de scroll**
- El header sticky evita desplazamientos de retorno; no usar `scrollToTop`.

---

## 7) Accesibilidad (a11y) e i18n

- `heading` con `level="1"` para semántica.
- Controles con `label` explícito o accesible.
- Estados/acciones **traducidos** (`__()`); evitar texto duro en Blade/Livewire.
- Atajos del navegador: *Enter* envía el formulario; *Esc* no debería cerrar nada (no hay modales por defecto).

---

## 8) Rendimiento y seguridad

- `wire:model.defer` en inputs para evitar renders excesivos.
- Evitar consultas innecesarias en `render()`; cargar listas en `mount()`.
- Sanitizar/validar siempre del lado del servidor; no confiar en solo `required` del input.

---

## 9) Uso como **componente reutilizable** `<x-simple-form>`

> Para acelerar formularios simples, se provee el componente Blade `<x-simple-form>` que implementa el estándar visual (header sticky, separadores full-width, barra inferior compacta, dark mode) y el **action-message**.

### Props principales
- `submit` *(string)*: método Livewire (ej. `save`).
- `editMode` *(bool)*: alterna labels de crear/editar.
- `backRoute` *(string)*: URL/route del botón Volver.
- `backModel` *(string, default `back`)*: nombre del `wire:model` para el checkbox “Volver al listado”.
- `maxWidth` *(string, default `max-w-3xl`)*: ancho del formulario.
- `showBack` *(bool, default `true`)*: muestra/oculta botón Volver.
- `showBackCheck` *(bool, default `true`)*: muestra/oculta checkbox “Volver al listado”.
- `showSavedMessage` *(bool, default `true`)*: muestra/oculta `<x-tenant.action-message on="saved"/>`.
- `savedLabel` *(string, default `__('site.saved')`)*: texto del action-message.
- **i18n**: `titleNew`, `titleEdit`, `subNew`, `subEdit`, `createLabel`, `updateLabel`, `backLabel`, `backListLabel`.

### Slots
- `{{ $slot }}`: **inputs/campos** del formulario (único obligatorio).
- `@slot('actions') ... @endslot`: acciones extra (header, derecha).
- `@slot('footerActions') ... @endslot`: acciones extra (barra inferior).

### Requisitos Livewire
- Props: `public bool $editMode`, `public bool $back`.
- Método: `save()` debe llamar a `$this->dispatch('saved')` y redirigir si `$back` es `true`.

### Ejemplo mínimo (uso real)
```blade
<x-simple-form
  submit="save"
  :edit-mode="$editMode"
  :back-route="route('tenant.dashboard.entities.index')"
  :back-model="'back'"
  :max-width="'max-w-2xl'"
  :show-saved-message="true"
  title-new="{{ __('entity.new_title') }}"
  title-edit="{{ __('entity.edit_title') }}"
  sub-new="{{ __('entity.new_subheading') }}"
  sub-edit="{{ __('entity.edit_subheading') }}"
  create-label="{{ __('entity.create_button') }}"
  update-label="{{ __('entity.update_button') }}"
  back-label="{{ __('site.back') }}"
  back-list-label="{{ __('site.back_list') }}"
>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <flux:input wire:model.defer="name"  label="{{ __('entity.name') }}"  required autocomplete="off" />
    <flux:input wire:model.defer="email" label="{{ __('entity.email') }}" type="email" required autocomplete="off" />
  </div>
  @error('name')  <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
  @error('email') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror

  {{-- Opcionales: slots de acciones adicionales --}}
  @slot('actions')
    {{-- <flux:button size="sm" variant="ghost">Ayuda</flux:button> --}}
  @endslot
  @slot('footerActions')
    {{-- <flux:button size="sm" variant="ghost">Otra acción</flux:button> --}}
  @endslot
</x-simple-form>
```

---

## 10) Ejemplo canónico (Blade)

```blade
<div class="flex items-start max-md:flex-col">
  <div class="flex-1 self-stretch w-full max-md:pt-6">
    <form wire:submit.prevent="save" class="space-y-6">
      <!-- Header sticky -->
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

      <!-- Contenido -->
      <div class="max-w-3xl space-y-4 pt-2">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <flux:input wire:model.defer="name" label="{{ __('entity.name') }}" required autocomplete="off" />
          <flux:input wire:model.defer="email" label="{{ __('entity.email') }}" type="email" required autocomplete="off" />
        </div>
        @error('name') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
        @error('email') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror

        <div>
          <flux:label class="text-xs">{{ __('entity.role') }}</flux:label>
          <div class="flex flex-wrap gap-4 mt-2">
            @foreach ($roles as $id => $name)
              <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-800 dark:text-neutral-200">
                <input type="radio" class="form-radio accent-blue-600 dark:accent-blue-400 rounded focus:ring-2 focus:ring-blue-500" value="{{ $name }}" wire:model="role">
                <span>{{ ucfirst($name) }}</span>
              </label>
            @endforeach
          </div>
          @error('role') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <flux:input wire:model.defer="password" label="{{ $editMode ? __('entity.new_password') : __('entity.password') }}" type="password" autocomplete="new-password" :required="!$editMode" />
          <flux:input wire:model.defer="password_confirmation" label="{{ __('entity.password_confirmation') }}" type="password" autocomplete="new-password" :required="!$editMode" />
        </div>

        <!-- Barra inferior compacta -->
        <div class="pt-6 max-w-3xl">
          <div class="flex justify-end gap-3 items-center text-sm opacity-80">
            <flux:checkbox size="sm" label="{{ __('site.back_list') }}" wire:model.live="back" />
            <flux:button as="a" variant="ghost" href="{{ route('<…index>') }}" size="sm">{{ __('site.back') }}</flux:button>
            <flux:button type="submit" size="sm">{{ $editMode ? __('entity.update_button') : __('entity.create_button') }}</flux:button>
          </div>
        </div>
      </div>

      <!-- Separador inferior -->
      <flux:separator variant="subtle" class="mt-8" />
    </form>
  </div>
</div>
```

---

## 10) Esqueleto Livewire mínimo

```php
class Form extends Component
{
    public ?int $id = null;
    public string $name = '';
    public string $email = '';
    public string $role = '';
    public array $roles = [];

    public string $password = '';
    public string $password_confirmation = '';

    public bool $back = false;
    public bool $editMode = false;

    public function mount(?Model $entity = null)
    {
        $this->roles = Role::orderBy('name')->pluck('name', 'id')->toArray();
        if ($entity && $entity->exists) {
            $this->id = $entity->id;
            $this->name = $entity->name;
            $this->email = $entity->email;
            $this->role = $entity->roles->pluck('name')->first() ?? '';
            $this->editMode = true;
        }
    }

    public function save()
    {
        $rules = [
            'name'  => 'required|string|max:255',
            'email' => ['required','email','max:255', Rule::unique('entities','email')->ignore($this->id)],
            'role'  => ['required', Rule::in(array_values($this->roles))],
        ];
        if (!$this->editMode || $this->password) {
            $rules['password'] = 'required|min:8|confirmed';
        }

        $data = $this->validate($rules);

        $entity = $this->editMode ? Entity::findOrFail($this->id) : new Entity();
        $entity->fill(['name'=>$this->name,'email'=>$this->email]);
        if ($this->password) { $entity->password = Hash::make($this->password); }
        $entity->save();
        $entity->syncRoles([$this->role]);

        $this->dispatch('saved');
        if ($this->back) { return redirect()->route('entities.index'); }
        session()->flash('success', $this->editMode ? __('entity.updated') : __('entity.created'));
    }
}
```

---

$1- [ ] **Action message** `x-tenant.action-message` colocado **antes del checkbox** en header y barra inferior.

- [ ] **Header sticky** con `bg-inherit` + `backdrop-blur`, CTA a la derecha, `z-30`.
- [ ] **Separador superior** `variant="subtle"` a ancho completo.
- [ ] Contenido alineado con `max-w-3xl`, grilla `md:grid-cols-2`, `gap-6`.
- [ ] Inputs con `wire:model.defer` y labels consistentes.
- [ ] Radios/checkboxes con `accent-blue-600` + *dark* `accent-blue-400`; `focus:ring-2`.
- [ ] Validaciones discretas `text-red-500 text-xs` junto al campo.
- [ ] **Barra inferior compacta** (no sticky), `opacity-80`, mismas acciones.
- [ ] **Separador inferior** sutil a ancho completo.
- [ ] i18n para títulos, subtítulos, labels y botones (`__()`).
- [ ] `save()` con validación, `dispatch('saved')`, redirect condicional por `$back` y flash de éxito.

---

## 12) Errores comunes a evitar

- Duplicar sombras/bordes → Mantener minimalismo (sin sombras, sin bordes extras).
- Desalinear anchos → Header, contenido y barra inferior **siempre** `max-w-3xl`.
- Botón *Volver* como link suelto → usar `<flux:button as="a" …>`.
- Omitir `autocomplete` correcto en contraseñas.
- Mezclar `wire:model.live` en inputs de texto → usar **`wire:model.defer`**.

---

## 13) Claves i18n sugeridas

- `entity.new_title`, `entity.new_subheading`, `entity.edit_title`, `entity.edit_subheading`
- `entity.create_button`, `entity.update_button`
- `entity.name`, `entity.email`, `entity.role`, `entity.password`, `entity.password_confirmation`, `entity.new_password`
- `entity.created`, `entity.updated`
- `site.back`, `site.back_list`

> **Uso**: Copiar la estructura y adaptar labels/campos. Este estándar asegura coherencia, accesibilidad y velocidad de implementación en todos los formularios simples de FitTrack.

