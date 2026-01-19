# 🧪 Guía de Testing - Sistema de Gamificación Integrado

## Verificación Rápida (5 minutos)

### Pre-requisitos
```bash
# 1. Asegurar que las migraciones están corridas
php artisan tenants:migrate

# 2. Inicializar perfil de gamificación para estudiante de prueba (opcional)
php artisan tinker
```

```php
// En tinker:
$tenant = \App\Models\Central\Tenant::first();
tenancy()->initialize($tenant);

$student = \App\Models\Tenant\Student::first();

// Verificar o crear perfil
if (!$student->gamificationProfile) {
    $profile = \App\Models\Tenant\StudentGamificationProfile::create([
        'student_id' => $student->id,
        'total_xp' => 150, // XP inicial para testing
        'current_level' => 2,
        'current_tier' => 1,
    ]);
    echo "✅ Perfil creado con 150 XP, Nivel 2, Tier Principiante\n";
} else {
    echo "✅ Perfil existente: {$student->gamificationProfile->total_xp} XP\n";
}
```

---

## Test 1: Vista de Dashboard

### Navegar a Dashboard del Alumno
1. Login como alumno: `http://fittrack.test/tenant/student/login`
2. Ir a dashboard: `http://fittrack.test/tenant/student/dashboard`

### ✅ Debe Mostrar:
- **Columna derecha:** Card "Mi Evolución" con:
  - Badge de tier (ej: 🌱 Principiante)
  - Nivel actual en grande
  - Barra de progreso con porcentaje
  - XP actual y XP para próximo nivel
  - Estadísticas adicionales

### 📸 Screenshot Esperado:
```
┌─────────────────────────────────┐
│ Mi Evolución                    │
├─────────────────────────────────┤
│  🌱 Principiante                │
│                                 │
│  Nivel 2                        │
│                                 │
│  ▓▓▓▓▓▓░░░░░░░░░░░  35%        │
│  150 XP         300 XP          │
│                                 │
│  📊 XP Total: 150               │
│  🎯 Próximo nivel: 150 XP más   │
└─────────────────────────────────┘
```

---

## Test 2: Vista de Entrenamiento

### Navegar a Entrenamiento de Hoy
1. Desde dashboard, click en "Comenzar entrenamiento"
2. O directo: `http://fittrack.test/tenant/student/workout-today`

### ✅ Debe Mostrar:

#### 1. Barra de Progreso de Nivel (justo debajo del header)
```
┌──────────────────────────────────────────────────────────────────┐
│ Nv. 2  🌱  ▓▓▓▓▓░░░░░░░░░░░░░░ 35%  Nv. 3  [🌱 Principiante]   │
│           150 XP           300 XP                                 │
└──────────────────────────────────────────────────────────────────┘
```

#### 2. Ejercicios con Checkbox
- Lista de ejercicios con checkbox para marcar completado

---

## Test 3: Completar Ejercicio (🎯 CLAVE)

### Pasos:
1. En la vista de entrenamiento, marca un checkbox de ejercicio como completado
2. **Observa:**

### ✅ Debe Suceder:

#### A. Notificación Flotante (Esquina Superior Derecha)
```
┌────────────────────────┐
│  ⚡  +10 XP            │ ← Aparece con animación
│  ¡Ejercicio           │    desde la derecha
│   completado!         │
└────────────────────────┘
```

**Características:**
- Aparece con animación de deslizamiento + escala
- Fondo: Gradiente con colores de marca
- Duración: 2.5 segundos
- Desaparece suavemente hacia arriba

#### B. Console Log (F12 → Console)
Si tienes debug activado, deberías ver:
```
Livewire: xp-gained event fired
{ xp: 10, level: 2 }
```

#### C. Barra de Progreso (se actualiza en próximo render)
- El porcentaje no cambia inmediatamente (proceso async)
- Refrescar página para ver actualización

---

## Test 4: Anti-Farming

### Pasos:
1. Marca un ejercicio como completado → Ganas XP ✅
2. Desmarca el mismo ejercicio
3. Vuelve a marcarlo como completado

### ✅ Debe Suceder:
- **Primera vez:** Notificación "+10 XP" (o 15/20 según nivel del ejercicio)
- **Segunda vez (mismo día):** NO aparece notificación (ya ganaste XP hoy)

### Verificación en Base de Datos:
```sql
SELECT * FROM exercise_completion_logs 
WHERE student_id = ? 
  AND exercise_id = ? 
  AND completed_date = CURDATE();
```
**Resultado esperado:** 1 fila (no duplicados)

---

## Test 5: Subida de Nivel

### Setup (en tinker):
```php
// Darle suficiente XP para subir de nivel
$student = \App\Models\Tenant\Student::first();
$profile = $student->gamificationProfile;
$profile->update(['total_xp' => 299]); // Casi en nivel 3 (300 XP)
```

### Pasos:
1. Refrescar vista de entrenamiento
2. Marcar ejercicio como completado (deberías ganar 10+ XP)
3. Refrescar página

### ✅ Debe Suceder:
- Barra de progreso se resetea (cerca de 0%)
- Nivel aumenta a 3
- Badge de tier puede cambiar si alcanzaste nuevo tier

---

## Test 6: Múltiples Notificaciones

### Pasos:
1. Marca rápidamente 3-4 ejercicios consecutivos

### ✅ Debe Suceder:
- Notificaciones se apilan verticalmente
- Cada una aparece con delay
- Cada una desaparece después de 2.5s
- No hay overlap visual

---

## Test 7: Responsive (Mobile)

### Pasos:
1. F12 → Toggle device toolbar (Ctrl+Shift+M)
2. Selecciona iPhone o Android
3. Navega a dashboard y entrenamiento

### ✅ Debe Mostrar:
- Notificaciones XP visibles (no cortadas)
- Barra de progreso se ajusta al ancho
- Widget de dashboard apilado verticalmente
- Texto legible en pantalla pequeña

---

## Troubleshooting

### ❌ No aparece notificación XP
**Causas posibles:**
1. Ejercicio no tiene `exercise_id` en `exercisesData`
   - **Fix:** Verificar migración de workouts, debe tener campo `exercise_id`

2. Perfil de gamificación no existe
   - **Fix:** Correr script de inicialización (ver Pre-requisitos)

3. Alpine.js no cargó
   - **Fix:** Verificar consola del browser (F12), buscar errores JS

4. Cache de Livewire
   - **Fix:** `php artisan livewire:clear-cache`

### ❌ Barra de progreso no se actualiza
**Causa:** El listener `AwardExperiencePoints` es queued (async)
- **Fix:** 
  - Esperar 5-10 segundos
  - Refrescar página manualmente
  - O correr queue worker: `php artisan queue:work`

### ❌ Widget no aparece en dashboard
**Causa:** Perfil de gamificación no existe
- **Fix:** Correr script de inicialización (ver Pre-requisitos)

### ❌ Duplica XP en mismo día
**Causa:** Constraint UNIQUE no está aplicado
- **Fix:** 
  ```bash
  php artisan tenants:migrate:fresh
  php artisan tenants:seed
  ```

---

## Verificación de Datos

### Query 1: Ver perfil de gamificación
```sql
SELECT s.first_name, s.last_name, 
       g.total_xp, g.current_level, g.current_tier
FROM students s
JOIN student_gamification_profiles g ON g.student_id = s.id
ORDER BY g.total_xp DESC;
```

### Query 2: Ver últimos ejercicios completados
```sql
SELECT e.name, 
       ecl.xp_awarded, 
       ecl.completed_at,
       s.first_name
FROM exercise_completion_logs ecl
JOIN exercises e ON e.id = ecl.exercise_id
JOIN students s ON s.id = ecl.student_id
ORDER BY ecl.completed_at DESC
LIMIT 10;
```

### Query 3: Verificar anti-farming
```sql
SELECT student_id, exercise_id, completed_date, COUNT(*) as count
FROM exercise_completion_logs
GROUP BY student_id, exercise_id, completed_date
HAVING count > 1;
```
**Resultado esperado:** 0 filas (no duplicados)

---

## Checklist Final

- [ ] Dashboard muestra widget "Mi Evolución"
- [ ] Entrenamiento muestra barra de progreso de nivel
- [ ] Marcar ejercicio dispara notificación "+XX XP"
- [ ] Notificación tiene animación smooth
- [ ] No se duplica XP en mismo día
- [ ] Barra de progreso actualiza después de ganar XP
- [ ] Responsive funciona en mobile
- [ ] No hay errores en consola JS
- [ ] No hay errores PHP en logs

---

## Video Tutorial (Opcional)

### Grabación sugerida:
1. Login como alumno
2. Mostrar dashboard con widget
3. Ir a entrenamiento
4. Marcar ejercicio → mostrar notificación
5. Refrescar → mostrar barra actualizada
6. Marcar varios ejercicios rápido
7. Mostrar responsive en mobile

**Duración:** ~2 minutos

---

## Soporte

Si algo no funciona como esperado:

1. **Verificar logs:** `storage/logs/laravel.log`
2. **Console browser:** F12 → Console (errores JS)
3. **Network tab:** F12 → Network (ver requests Livewire)
4. **Tinker debug:**
   ```php
   $student = \App\Models\Tenant\Student::first();
   $student->gamificationProfile; // Debe existir
   gamification_stats($student); // Helper debe retornar array
   ```

---

¡Listo! Con estos tests deberías poder validar que todo el sistema de gamificación funciona correctamente 🎉
