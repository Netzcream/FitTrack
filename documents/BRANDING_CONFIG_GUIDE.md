# Branding — Configuración mínima

El branding se guarda en la tabla `configuration` del tenant. Todas las respuestas API incluyen `branding`.

## Campos
- `brand_name`
- `trainer_name`
- `trainer_email`
- `logo_url`
- `logo_light_url`
- `primary_color`
- `secondary_color`
- `accent_color`

## Configurar vía Tinker (rápido)
```php
use App\Models\Configuration;

Configuration::setConf('brand_name', "Juan's Coaching");
Configuration::setConf('trainer_name', 'Juan Pérez');
Configuration::setConf('trainer_email', 'juan@example.com');
Configuration::setConf('logo_url', 'https://example.com/logo.png');
Configuration::setConf('logo_light_url', 'https://example.com/logo-light.png');
Configuration::setConf('primary_color', '#3B82F6');
Configuration::setConf('secondary_color', '#10B981');
Configuration::setConf('accent_color', '#F59E0B');
```

## Uso en la app
Aplicar colores y logo desde la respuesta API (`branding`).

## Proyección
UI de branding en dashboard (pendiente si no existe).# 🎨 Guía: Configurar Branding para tu App Móvil

Esta guía explica cómo configurar el logo, colores y datos del trainer para que aparezcan en la app móvil (Next.go).

---

## 📍 Dónde Configurar

Todas las opciones se guardan en la tabla `configuration` del tenant (base de datos).

### Opción 1: Dashboard (Recomendado)

**Por implementar:** Crear sección en `Configuración` → `Branding` donde el trainer pueda:
- Subir logo (PNG/SVG)
- Seleccionar colores (color picker)
- Guardar nombre y email

### Opción 2: Código (Administrador)

```php
// En tinker o migrations
use App\Models\Configuration;

// Guardar logo
Configuration::setConf('logo_url', 'https://example.com/logo.png');

// Guardar colores
Configuration::setConf('primary_color', '#3B82F6');
Configuration::setConf('secondary_color', '#10B981');
Configuration::setConf('accent_color', '#F59E0B');

// Datos del trainer
Configuration::setConf('trainer_name', 'Juan Pérez');
Configuration::setConf('trainer_email', 'juan@example.com');
Configuration::setConf('brand_name', "Juan's Coaching");
```

---

## 🎯 Campos Disponibles

| Campo | Key | Tipo | Ejemplo | Requerido |
|-------|-----|------|---------|-----------|
| **Nombre Marca** | `brand_name` | string | "Juan's Coaching" | No |
| **Nombre Trainer** | `trainer_name` | string | "Juan Pérez" | No |
| **Email Trainer** | `trainer_email` | string | "juan@example.com" | No |
| **Logo URL** | `logo_url` | url | "https://example.com/logo.png" | No |
| **Logo (Light)** | `logo_light_url` | url | "https://example.com/logo-light.png" | No |
| **Color Primario** | `primary_color` | hex | "#3B82F6" | No |
| **Color Secundario** | `secondary_color` | hex | "#10B981" | No |
| **Color Acento** | `accent_color` | hex | "#F59E0B" | No |

---

## 🎨 Colores Predeterminados

Si no configuras colores, se usan estos:

```json
{
  "primary_color": "#3B82F6",      // Azul
  "secondary_color": "#10B981",    // Verde
  "accent_color": "#F59E0B"        // Ámbar
}
```

---

## 📸 Subir Logo

### Desde Blade (Formulario)

```blade
<form action="{{ route('config.update.logo') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="logo" accept="image/*" required>
    <button type="submit">Subir Logo</button>
</form>
```

### Controller (Guardar)

```php
public function updateLogo(Request $request)
{
    $file = $request->file('logo');
    
    // Guardar en storage público
    $path = $file->store('branding', 'public');
    $url = asset('storage/' . $path);
    
    // Guardar en configuración
    Configuration::setConf('logo_url', $url);
    
    return back()->with('success', 'Logo updated');
}
```

---

## 🎨 Seleccionar Colores

### Desde Blade (Color Picker)

```blade
<div class="space-y-4">
    <div>
        <label>Color Primario</label>
        <input type="color" name="primary_color" 
               value="{{ tenant_config('primary_color', '#3B82F6') }}">
    </div>
    
    <div>
        <label>Color Secundario</label>
        <input type="color" name="secondary_color" 
               value="{{ tenant_config('secondary_color', '#10B981') }}">
    </div>
    
    <div>
        <label>Color Acento</label>
        <input type="color" name="accent_color" 
               value="{{ tenant_config('accent_color', '#F59E0B') }}">
    </div>
</div>
```

### Controller (Guardar)

```php
public function updateColors(Request $request)
{
    $request->validate([
        'primary_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
        'secondary_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
        'accent_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
    ]);
    
    Configuration::setConf('primary_color', $request->primary_color);
    Configuration::setConf('secondary_color', $request->secondary_color);
    Configuration::setConf('accent_color', $request->accent_color);
    
    return back()->with('success', 'Colors updated');
}
```

---

## ✅ Verificar Configuración

### Desde Tinker

```php
php artisan tinker

use App\Services\Tenant\BrandingService;

BrandingService::getBrandingData();
// Retorna array con toda la información de branding
```

### Desde API

```bash
curl -X GET http://localhost:8000/api/profile \
  -H "Authorization: Bearer {token}" \
  -H "X-Tenant-ID: {tenant_id}"

# En la respuesta verás:
# "branding": {
#   "brand_name": "...",
#   "logo_url": "...",
#   "primary_color": "...",
#   ...
# }
```

---

## 🎯 Mejores Prácticas

### Logo

- ✅ **Formato:** PNG con fondo transparente (recomendado) o SVG
- ✅ **Tamaño:** 200x200px mínimo, máx 1000x1000px
- ✅ **Peso:** < 500KB
- ✅ **Dos versiones:** 
  - `logo_url` - Para fondo claro/normal
  - `logo_light_url` - Para fondo oscuro (opcional)

### Colores

- ✅ **Formato:** Hex (#RRGGBB)
- ✅ **Contraste:** Asegurar que el primario tenga suficiente contraste con blanco/negro
- ✅ **Consistencia:** Usar colores coherentes con la marca del trainer
- ✅ **Prueba:** Visualizar en la app antes de publicar

### Datos del Trainer

- ✅ **Nombre:** Nombre completo (ej: "Juan Pérez")
- ✅ **Email:** Email válido (ej: "juan@example.com")
- ✅ **Brand Name:** Nombre del gym/coaching (ej: "Juan's Coaching")

---

## 🔧 Troubleshooting

### El branding no se ve en la app

1. Verificar que los valores estén guardados:
```php
php artisan tinker
tenant_config('logo_url')  // Debe retornar URL
tenant_config('primary_color')  // Debe retornar hex color
```

2. Verificar que el tenant_id sea correcto en el header
```
X-Tenant-ID: {tenant_id}
```

3. Limpiar cache:
```bash
php artisan config:clear
php artisan cache:clear
```

### El logo no carga

- Verificar que la URL sea accesible desde la app
- Si usa storage local, verificar que esté en `public/storage`
- Usar URLs absolutas (con dominio completo)

```
❌ /storage/branding/logo.png
✅ https://example.com/storage/branding/logo.png
```

### Los colores se ven raros

- Verificar que el formato sea hex válido: `#RRGGBB`
- Probar en un color picker: https://htmlcolorcodes.com
- Asegurar suficiente contraste con backgrounds

---

## 📱 Vista en la App (Next.go)

La app automaticamente:

1. Carga branding en el login
2. Aplica colores CSS:
   ```css
   :root {
     --primary-color: #3B82F6;
     --secondary-color: #10B981;
     --accent-color: #F59E0B;
   }
   ```

3. Muestra el logo en:
   - Header/navbar
   - Splash screen
   - Perfil del estudiante

4. Usa colores en:
   - Botones principales
   - Links y acciones
   - Badges y highlights
   - Gráficos y charts

---

## 💡 Ejemplos

### Setup Completo

```php
// Guardar branding para "Juan's Coaching"
Configuration::setConf('brand_name', "Juan's Coaching");
Configuration::setConf('trainer_name', 'Juan Pérez');
Configuration::setConf('trainer_email', 'juan@coach.com');
Configuration::setConf('logo_url', 'https://cdn.example.com/juans-logo.png');
Configuration::setConf('primary_color', '#DC2626');    // Rojo
Configuration::setConf('secondary_color', '#0284C7');  // Azul
Configuration::setConf('accent_color', '#F97316');    // Naranja
```

### Obtener Branding

```php
use App\Services\Tenant\BrandingService;

$branding = BrandingService::getBrandingData();

echo $branding['brand_name'];      // "Juan's Coaching"
echo $branding['logo_url'];        // "https://..."
echo $branding['primary_color'];   // "#DC2626"
```

---

## 🚀 Next Steps

- [ ] Crear formulario de branding en dashboard
- [ ] Agregar preview de colores en tiempo real
- [ ] Implementar cropper de imagen para logo
- [ ] Guardar logo en storage cloud (S3, GCS, etc)
- [ ] Generar favicon dinámico del color primario
- [ ] Exportar como JSON para configuración de app

---

**Última actualización:** Enero 2026
