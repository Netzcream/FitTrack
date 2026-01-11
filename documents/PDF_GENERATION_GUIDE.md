# Guía de Generación de PDFs para Planes de Entrenamiento

## Descripción General

El sistema de generación de PDFs permite a los alumnos descargar sus planes de entrenamiento en formato PDF profesional, incluyendo:

- **Logo del tenant** personalizado
- **Colores de marca** del tenant (color base y color oscuro)
- **Imágenes de ejercicios** con estilos profesionales
- **Información completa del plan** (versión, fechas, objetivos)
- **Espacio para registro de progreso** por semana

## Archivos Principales

### 1. Controlador
**Archivo:** `app/Http/Controllers/Tenant/StudentPlanController.php`

Contiene dos métodos principales:

#### `downloadAssignment(StudentPlanAssignment $assignment)`
- Usado para descargar asignaciones de planes (modelo nuevo con snapshot)
- Verifica que el alumno tenga acceso al plan
- Carga los ejercicios completos con sus imágenes desde la base de datos
- Enriquece los datos del snapshot con objetos `Exercise` completos
- Pasa variables de colores y logo al PDF

#### `download(TrainingPlan $plan)`
- Método de retrocompatibilidad para planes antiguos
- Usado también por entrenadores para descargar plantillas
- Similar al anterior pero trabaja con `TrainingPlan` directamente

### 2. Vista PDF
**Archivo:** `resources/views/pdf/student/training-plan.blade.php`

Estructura del PDF:

1. **Encabezado**
   - Logo del tenant (izquierda)
   - Nombre del plan y descripción (derecha)
   - Línea divisoria con color de marca

2. **Información del Plan**
   - Nombre del alumno
   - Versión del plan
   - Meta y duración
   - Fechas de vigencia

3. **Días y Ejercicios**
   - Título del día con gradiente de colores
   - Cada ejercicio incluye:
     - Nombre y categoría
     - Detalle (series/repeticiones)
     - Descripción
     - Notas (con fondo amarillo si existen)
     - Imágenes del ejercicio (con borde del color de marca)
     - Tabla de progreso semanal

4. **Pie de página**
   - Logo FitTrack y nombre del tenant
   - Fecha de generación

## Personalización por Tenant

El sistema utiliza las siguientes configuraciones del tenant:

### Colores
```php
$colorBase = tenant_config('color_base', '#263d83');  // Color principal
$colorDark = tenant_config('color_dark', '#1e2a5e');  // Color oscuro
```

Estos colores se aplican en:
- Títulos del plan (h1)
- Líneas divisoras
- Títulos de días (gradiente)
- Bordes de ejercicios
- Bordes de imágenes
- Encabezados de tablas

### Logo
```php
$logo = tenant()->config?->getFirstMediaUrl('logo');
```

El logo se muestra:
- En el encabezado del PDF (esquina superior izquierda)
- Máximo 70px de alto x 220px de ancho

### Favicon
Aunque no se usa en el PDF, el favicon del tenant está disponible en:
```php
tenant()->config?->getFirstMediaUrl('favicon');
```

## Imágenes de Ejercicios

Las imágenes se manejan a través de Spatie Media Library:

```php
// Verificar si el ejercicio tiene imágenes
if ($exercise && $exercise->hasMedia('images')) {
    // Obtener todas las imágenes
    foreach ($exercise->getMedia('images') as $media) {
        $imagePath = $media->getPath(); // Ruta física del archivo
        // Renderizar imagen
    }
}
```

Características:
- Tamaño: 110x110 px
- Borde: 2px con color de marca
- Padding: 4px
- Border-radius: 8px
- Box-shadow para efecto de profundidad

## Rutas Disponibles

### Para Alumnos
```php
Route::get('/plan/{assignment}/download', [StudentPlanController::class, 'downloadAssignment'])
    ->name('tenant.student.download-plan');
```

**Uso en Blade:**
```blade
<a href="{{ route('tenant.student.download-plan', $assignment->uuid) }}">
    Descargar PDF
</a>
```

### Para Entrenadores (Backward Compatibility)
```php
Route::get('/plan/{plan}/download', [StudentPlanController::class, 'download']);
```

## Configuración de DomPDF

**Archivo:** `config/dompdf.php`

Configuraciones importantes:
- Fuente por defecto: DejaVu Sans (soporta caracteres UTF-8)
- Tamaño de papel: A4
- Orientación: Portrait
- Márgenes: 30px (superior/inferior), 40px (laterales)

## Troubleshooting

### Las imágenes no aparecen
1. Verificar que los ejercicios tengan imágenes cargadas en Media Library
2. Verificar permisos de la carpeta `storage/app/public`
3. Confirmar que `file_exists($imagePath)` retorna `true`

### El logo no aparece
1. Verificar que el tenant tenga un logo configurado en Apariencia
2. Verificar ruta del logo: `tenant()->config?->getFirstMediaUrl('logo')`
3. Confirmar permisos de lectura del archivo

### Los colores no se aplican
1. Verificar configuración en: Configuración > Apariencia
2. Los colores deben estar en formato hexadecimal (#RRGGBB)
3. Revisar que `tenant_config()` funcione correctamente

### Errores de memoria
Si el PDF tiene muchas imágenes:
1. Aumentar `memory_limit` en php.ini
2. Optimizar imágenes antes de subirlas
3. Considerar reducir el tamaño de las imágenes en el PDF

## Mejoras Futuras

- [ ] Agregar opción de marca de agua personalizada
- [ ] Permitir seleccionar orientación (Portrait/Landscape)
- [ ] Agregar más estilos de diseño predefinidos
- [ ] Incluir estadísticas del alumno si están disponibles
- [ ] Opción de enviar PDF por email
- [ ] Generar versión imprimible sin colores

## Ejemplo de Uso

```php
// En un controlador
use App\Models\Tenant\StudentPlanAssignment;
use Barryvdh\DomPDF\Facade\Pdf;

$assignment = StudentPlanAssignment::findOrFail($uuid);

// Generar PDF
$pdf = Pdf::loadView('pdf.student.training-plan', [
    'assignment' => $assignment,
    'student'    => $assignment->student,
    'grouped'    => $groupedExercises,
    'logo'       => $logoPath,
    'colorBase'  => '#263d83',
    'colorDark'  => '#1e2a5e',
])->setPaper('a4');

// Descargar
return $pdf->download('plan.pdf');

// O mostrar en navegador
return $pdf->stream('plan.pdf');
```

## Referencias

- [DomPDF Documentation](https://github.com/dompdf/dompdf)
- [Barryvdh Laravel DomPDF](https://github.com/barryvdh/laravel-dompdf)
- [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary)
