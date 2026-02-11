# 🧩 Guía de desarrollo - *Models y Migrations* (Estándar FitTrack Unificado)

> **Objetivo:** definir un estándar coherente y minimalista para la creación de **Models Eloquent y Migrations** en FitTrack.  
> Se prioriza **claridad, consistencia y simplicidad** sobre complejidad innecesaria.  
> Cada modelo debe ser *autoexpresivo*, predecible y fácil de mantener.

---

## 1️⃣ Principios generales

1. **Simplicidad primero:**  
   Cada tabla/modelo debe incluir solo los campos realmente necesarios para describir la entidad.  
   Evitar "feature creep" (p. ej. `status_json`, `extra_data`, etc.) salvo que exista un caso de uso claro.

2. **Normalización práctica:**  
   - Reutilizar catálogos (`plans`, `objectives`, `payment_methods`, etc.) en lugar de repetir texto.  
   - Campos con relaciones **foreign key + constrained() + nullOnDelete()**.

3. **Datos ordenables y filtrables:**  
   - Siempre incluir `is_active` (booleano) y, si corresponde, `order` (entero sin signo).  
   - Los `scopes` deben exponer fácilmente `active()`, `search()`, `ordered()`.

4. **UUID obligatorio:**  
   - Todo modelo debe tener un campo `uuid` **único**.  
   - Se genera automáticamente en `boot()` o `booted()`.

5. **Slug opcional (semántico):**  
   - Si la entidad es visible públicamente o listable, incluir `slug` con generación automática.  
   - No duplicar UUID y slug: el primero es técnico, el segundo legible.

6. **SoftDeletes siempre que aplique:**  
   - Se usa `use SoftDeletes;` para evitar pérdidas de datos irreversibles.

7. **Media Library (Spatie):**  
   - Si el modelo requiere imágenes o archivos, debe implementar `HasMedia` + `InteractsWithMedia`.  
   - Reglas:  
     - Conversiones definidas (`thumb`, `cover`, `original`).  
     - No almacenar paths manuales ni URLs.  
     - Usar colecciones nombradas (`'avatar'`, `'gallery'`, `'attachments'`).

8. **Timestamps y consistencia:**  
   - Siempre `timestamps()` + `softDeletes()` en migrations.  
   - Nunca usar `nullableTimestamps()` ni desactivar timestamps.

---

## 2️⃣ Estructura base de una *Migration*

```php
Schema::create('entities', function (Blueprint $table) {
    $table->id();
    $table->uuid()->unique();
    $table->string('slug')->unique()->nullable();
    $table->string('name');
    $table->text('description')->nullable();

    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('order')->default(0);

    $table->json('meta')->nullable();

    $table->softDeletes();
    $table->timestamps();
});
```

### ✅ Convenciones
- `uuid` siempre `unique()`.
- `slug` opcional pero útil para URLs legibles.
- `is_active` → filtro lógico universal.
- `order` → control de orden manual.
- `json` → agrupación flexible de campos no frecuentes (`meta`, `config`, `options`).
- Fechas → usar tipos nativos (`date`, `datetime`).
- Relaciones → usar `foreignId()->constrained()->nullOnDelete();`.

---

## 3️⃣ Estructura base de un *Model*

```php
namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Entity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'slug',
        'name',
        'description',
        'is_active',
        'order',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta'      => 'array',
    ];

    /* ---------------- Scopes ---------------- */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;
        $t = "%{$term}%";
        return $q->where(function ($qq) use ($t) {
            $qq->where('name', 'like', $t)
               ->orWhere('slug', 'like', $t)
               ->orWhere('description', 'like', $t);
        });
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('order');
    }

    /* ---------------- Booted ---------------- */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->slug) && !empty($model->name)) {
                $base = Str::slug($model->name);
                $slug = $base;
                $i = 2;

                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }

                $model->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /* ---------------- Extra helpers ---------------- */
    public function __toString(): string
    {
        return $this->name ?? static::class;
    }
}
```

---

## 4️⃣ Convenciones adicionales

| Tema | Regla / Recomendación |
|------|------------------------|
| **Nombres de tabla** | Plural, snake_case (`students`, `commercial_plans`) |
| **Nombres de modelos** | Singular, PascalCase (`Student`, `CommercialPlan`) |
| **Claves foráneas** | `foreignId('entity_id')->constrained()->nullOnDelete();` |
| **Booleanos** | Prefijo `is_` o `has_` (`is_active`, `has_trial`) |
| **Fechas** | `*_at` o `*_on` (`due_date`, `paid_at`, `expires_on`) |
| **Orden** | Campo `order` entero; `scopeOrdered()` para consistencia |
| **Casts** | Siempre tipar `boolean`, `array`, `date`, `datetime`, `decimal:n` |
| **Media Library** | Definir método `registerMediaCollections()` y conversiones |
| **Relaciones** | Declarar explícitamente `hasMany`, `belongsTo`, etc. |
| **Ruteo** | Siempre usar `getRouteKeyName(): uuid` |
| **Método mágico** | `__toString()` devuelve el campo principal (`name`, `title`) |

---

## 5️⃣ Ejemplo completo con Media Library

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TrainerProfile extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = ['uuid','name','bio','is_active','avatar','meta'];

    protected $casts = ['is_active'=>'boolean','meta'=>'array'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->fit('crop', 150, 150);
    }
}
```

---

## 6️⃣ Checklist de revisión

- [ ] Incluye `uuid` y slug generados automáticamente.  
- [ ] Tiene `is_active` y/o `order`.  
- [ ] Usa `timestamps()` y `softDeletes()`.  
- [ ] Define `casts` correctamente (`boolean`, `array`, etc.).  
- [ ] Incluye scopes `search`, `active`, `ordered`.  
- [ ] Implementa `__toString()`.  
- [ ] Usa `getRouteKeyName()` → `'uuid'`.  
- [ ] Si tiene archivos, usa **Spatie Media Library**.  
- [ ] No contiene lógica de negocio excesiva (solo helpers simples).  

---

