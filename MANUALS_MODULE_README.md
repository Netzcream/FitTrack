# Módulo de Manuales y Guías - FitTrack Central

## Resumen de la implementación

Este módulo permite gestionar manuales y guías desde Central para ser consumidos por los tenants.

### ✅ Archivos creados

#### 1. **Modelo y Migración**
- `app/Models/Central/Manual.php` - Modelo principal con trait `CentralConnection`
- `database/migrations/2026_01_11_000001_create_manuals_table.php` - Estructura de la tabla

**Características del modelo:**
- UUID como identificador único
- Slug automático basado en el título
- Soft deletes
- Scopes: `active()`, `published()`, `byCategory()`, `search()`
- Métodos helper: `publish()`, `unpublish()`
- Accessors: `excerpt`, `readingTime`

#### 2. **Enum de Categorías**
- `app/Enums/ManualCategory.php`

**Categorías disponibles:**
- `configuration` - Configuración
- `training` - Entrenamiento
- `nutrition` - Nutrición
- `support` - Soporte
- `general` - General

Cada categoría incluye método `label()` e `icon()` para facilitar su uso en la UI.

#### 3. **Validación**
- `app/Http/Requests/Central/StoreManualRequest.php`
- `app/Http/Requests/Central/UpdateManualRequest.php`

**Validaciones incluidas:**
- Título requerido (max 255 caracteres)
- Slug único (generado automáticamente si no se proporciona)
- Categoría debe ser un valor válido del enum
- Resumen opcional (max 500 caracteres)
- Contenido requerido (longText para HTML enriquecido)
- Campos booleanos y de fecha con sus validaciones

#### 4. **Policy**
- `app/Policies/ManualPolicy.php`

**Permisos:**
- Ver listado: Todos los usuarios autenticados
- Ver detalle: Usuarios autenticados (solo publicados y activos) o super_admin (todos)
- Crear/Editar/Eliminar: Solo super_admin

Registrado en `AppServiceProvider.php`

#### 5. **Archivos de idioma**
- `resources/lang/es/manuals.php`

Incluye todas las traducciones necesarias para:
- Títulos y subtítulos
- Labels de formularios
- Mensajes de éxito/error
- Filtros y búsqueda
- Categorías y estados

#### 6. **Seeder**
- `database/seeders/ManualSeeder.php`

Crea 5 manuales de ejemplo cubriendo todas las categorías:
1. Configuración de perfil
2. Cómo crear una rutina de entrenamiento
3. Guía de nutrición básica
4. Soporte técnico y contacto
5. Primeros pasos en FitTrack

---

## 📋 Estructura de la tabla `manuals`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | ID autoincrementable |
| `uuid` | uuid | Identificador único (route key) |
| `title` | string | Título del manual |
| `slug` | string | Slug único generado automáticamente |
| `category` | string | Categoría (enum) |
| `summary` | text | Resumen breve (opcional) |
| `content` | longText | Contenido completo (HTML permitido) |
| `icon_path` | string | Ruta al ícono (para futura integración con Spatie) |
| `is_active` | boolean | Si está activo o no |
| `published_at` | timestamp | Fecha de publicación |
| `sort_order` | integer | Orden de visualización |
| `timestamps` | - | created_at, updated_at |
| `deleted_at` | timestamp | Soft delete |

**Índices creados:**
- `category`
- `is_active`
- `published_at`
- `sort_order`

---

## 🚀 Próximos pasos

### ✅ Fase 1: Controllers y Livewire (COMPLETADO)
- [x] Crear componente Livewire `ManualsIndex` para el listado
- [x] Crear componente Livewire `ManualsForm` para Create/Edit
- [x] Crear vistas Blade siguiendo la guía de diseño
- [x] Implementar rutas en `routes/web.php`

**Archivos creados en Fase 1:**
- `app/Livewire/Central/Dashboard/Manuals/ManualsIndex.php`
- `app/Livewire/Central/Dashboard/Manuals/ManualsForm.php`
- `resources/views/livewire/central/dashboard/manuals/index.blade.php`
- `resources/views/livewire/central/dashboard/manuals/form.blade.php`
- `resources/views/livewire/central/dashboard/manuals/partials/manuals-list.blade.php`

**Rutas agregadas:**
```php
Route::prefix('manuals')->name('manuals.')->group(function () {
    Route::get('/', ManualsIndex::class)->name('index');
    Route::get('/create', ManualsForm::class)->name('create');
    Route::get('/{manual}/edit', ManualsForm::class)->name('edit');
});
```

**URLs disponibles:**
- Index: `http://localhost/dashboard/manuals`
- Crear: `http://localhost/dashboard/manuals/create`
- Editar: `http://localhost/dashboard/manuals/{uuid}/edit`

**Funcionalidades implementadas:**
- ✅ Listado con paginación (15 por página)
- ✅ Búsqueda por título, resumen y contenido
- ✅ Filtro por categoría
- ✅ Ordenamiento por columnas (título, fecha actualización, orden)
- ✅ Badges de estado (publicado/activo/inactivo)
- ✅ Badges de categoría con colores
- ✅ Formulario completo de creación/edición
- ✅ Validación de datos
- ✅ Slug automático desde título
- ✅ Toggle para activar/desactivar
- ✅ Modal de confirmación para eliminar
- ✅ Modal de confirmación para salir sin guardar
- ✅ Notificaciones de éxito/error
- ✅ Dark mode completo
- ✅ Componentes reutilizables (`<x-data-table>`, `<x-index-filters>`)

### Fase 2: Mejoras de Editor (Pendiente)
- [ ] Instalar y configurar Spatie Media Library
- [ ] Agregar colecciones para íconos y archivos adjuntos
- [ ] Implementar upload de imágenes
- [ ] Implementar upload de PDFs, videos, etc.

### Fase 4: API para Tenants (Futuro)
- [ ] Endpoint para listar manuales publicados y activos
- [ ] Endpoint para obtener detalle de un manual
- [ ] Filtrado por categoría
- [ ] Búsqueda de manuales

---

## 🔧 Cómo ejecutar la migración

```bash
cd c:\laragon\www\FitTrack
php artisan migrate
php artisan db:seed --class=ManualSeeder
```

---

## 💡 Notas técnicas

1. **CentralConnection**: El modelo usa el trait `CentralConnection` de Stancl Tenancy, lo que significa que los datos se almacenan en la base de datos central y pueden ser consultados desde cualquier tenant.

2. **UUID como Route Key**: Se usa UUID en lugar del ID para mayor seguridad en las rutas públicas.

3. **Slug automático**: Si no se proporciona un slug, se genera automáticamente a partir del título usando `Str::slug()`.

4. **HTML en contenido**: El campo `content` acepta HTML enriquecido, preparado para usar con editores WYSIWYG.

5. **Sin archivos por ahora**: Los campos `icon_path` y la funcionalidad de archivos adjuntos están preparados pero no implementados, esperando la integración con Spatie Media Library.

6. **Preparado para i18n**: Aunque actualmente solo está en español, la estructura está lista para agregar más idiomas.

---

## 📚 Referencias

- Guía de diseño: `documents/disenio_ux/UX_guide-index.md`
- Modelo de referencia: `app/Models/Central/Conversation.php`
- Trait usado: `Stancl\Tenancy\Database\Concerns\CentralConnection`
