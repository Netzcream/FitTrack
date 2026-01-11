# Sistema de Cola de Planes de Entrenamiento

## 📋 Resumen

Este documento explica cómo funciona el sistema de gestión de planes de entrenamiento con cola y activación automática.

## 🔄 Flujo de Estados de un Plan

### Estados Posibles

1. **Activo Vigente** (`is_active = true`, `starts_at <= hoy <= ends_at`)
   - El plan actual que el alumno debe seguir
   - Solo puede haber **1 plan activo por alumno** (constraint en BD)

2. **Futuro Pendiente** (`is_active = false`, `starts_at > hoy`)
   - Plan programado para iniciar en el futuro
   - Se activa automáticamente cuando llega su fecha de inicio
   - Solo puede haber **1 plan futuro** por alumno (los anteriores se dan de baja)

3. **Vencido** (`is_active = false`, `ends_at < hoy`)
   - Plan que ya finalizó su período de vigencia
   - Se desactiva automáticamente al pasar su fecha de fin

## 🎯 Comportamiento al Asignar Nuevos Planes

### Escenario 1: Sin plan activo
```
Estado inicial: Sin planes activos
Acción: Asignar nuevo plan
Resultado: Plan se activa inmediatamente (is_active = true)
```

### Escenario 2: Con plan activo + "Empezar ya" ✓
```
Estado inicial: Plan A activo
Acción: Asignar Plan B con "Empezar ya" marcado
Resultado:
  - Plan A: is_active = false, ends_at = hoy
  - Plan B: is_active = true, empieza hoy
```

### Escenario 3: Con plan activo + Sin "Empezar ya"
```
Estado inicial: Plan A activo (vence 31/01/2026)
Acción: Asignar Plan B para iniciar 01/02/2026
Resultado:
  - Plan A: is_active = true (sigue activo)
  - Plan B: is_active = false, starts_at = 01/02/2026 (en cola)
```

### Escenario 4: Con plan futuro pendiente
```
Estado inicial: 
  - Plan A: activo (vence 31/01/2026)
  - Plan B: futuro (inicia 01/02/2026, is_active = false)
  
Acción: Asignar Plan C
Resultado:
  - Plan A: se mantiene o desactiva según "Empezar ya"
  - Plan B: ends_at = hoy (dado de baja automáticamente)
  - Plan C: nuevo plan (activo o en cola según "Empezar ya")
```

## ⚙️ Procesos Automáticos

### 1. Desactivar Planes Vencidos
**Comando:** `plans:deactivate-expired`  
**Frecuencia:** Diario a las 00:01  
**Función:**
```php
// Itera sobre todos los tenants activos
foreach (Tenant::where('status', 'active')->get() as $tenant) {
    $tenant->run(function () {
        // Busca planes activos cuya fecha de fin ya pasó
        StudentPlanAssignment::where('is_active', true)
            ->where('ends_at', '<', now())
            ->update(['is_active' => false]);
    });
}
```

### 2. Activar Planes Pendientes
**Comando:** `plans:activate-pending`  
**Frecuencia:** Diario a las 00:05  
**Función:**
```php
// Itera sobre todos los tenants activos
foreach (Tenant::where('status', 'active')->get() as $tenant) {
    $tenant->run(function () {
        // 1. Busca planes con is_active = false y starts_at <= hoy
        // 2. Desactiva cualquier plan activo del mismo estudiante
        // 3. Activa el plan pendiente (is_active = true)
    });
}
```

**⚠️ Nota:** Ambos comandos iteran sobre todos los tenants activos y ejecutan la lógica dentro del contexto de cada uno (usando `$tenant->run()`), ya que el sistema usa multitenancy.

## 🔍 Ejemplos Prácticos

### Ejemplo 1: Transición automática normal
```
10/01/2026 - Asignar Plan A (vigente hasta 31/01/2026)
Estado: Plan A activo

15/01/2026 - Asignar Plan B (inicia 01/02/2026)
Estado: 
  - Plan A: activo
  - Plan B: en cola (is_active = false)

01/02/2026 00:05 - Comando automático
Estado:
  - Plan A: desactivado (is_active = false, ends_at = 01/02/2026 00:05)
  - Plan B: activado (is_active = true)
```

### Ejemplo 2: Cambio de planes en cola
```
10/01/2026 - Plan A activo (vence 31/01)
15/01/2026 - Asignar Plan B (inicia 01/02)
Estado: Plan A activo, Plan B en cola

20/01/2026 - Asignar Plan C (inicia 01/02)
Estado: 
  - Plan A: activo
  - Plan B: dado de baja (ends_at = 20/01/2026)
  - Plan C: en cola (inicia 01/02)
```

## 📊 Consultas Útiles

### Ver plan activo de un alumno
```php
$student->currentPlanAssignment // Relación hasOne con is_active = true
```

### Ver plan futuro pendiente
```php
$student->planAssignments()
    ->where('is_active', false)
    ->where('starts_at', '>', now())
    ->first()
```

### Ver todos los planes (histórico)
```php
$student->planAssignments()
    ->orderByDesc('starts_at')
    ->get()
```

## 🛠️ Comandos Manuales

### Activar planes pendientes manualmente
```bash
php artisan plans:activate-pending
```

### Desactivar planes vencidos manualmente
```bash
php artisan plans:deactivate-expired
```

### Ver schedule configurado
```bash
php artisan schedule:list
```

### Ejecutar schedule manualmente (para testing)
```bash
php artisan schedule:run
```

## ⚠️ Consideraciones Importantes

1. **Solo 1 plan activo por alumno**: Constraint de base de datos lo garantiza
2. **Solo 1 plan futuro por alumno**: Se da de baja el anterior al asignar uno nuevo
3. **Transición automática**: Los comandos scheduled se ejecutan diariamente
4. **Multitenancy**: Los comandos iteran automáticamente sobre todos los tenants activos
5. **Cron requerido**: Asegurar que el servidor tiene configurado el cron de Laravel:
   ```bash
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```

## 🔐 Constraint de Base de Datos

```sql
-- Solo puede haber un plan activo por estudiante
ALTER TABLE student_plan_assignments 
ADD CONSTRAINT uniq_active_assignment_per_student 
UNIQUE (active_student_id)
WHERE active_student_id = IF(is_active, student_id, NULL);
```

## 📝 Archivos Relacionados

- **Servicio:** `app/Services/Tenant/AssignPlanService.php`
- **Modelo:** `app/Models/Tenant/StudentPlanAssignment.php`
- **Comandos:**
  - `app/Console/Commands/ActivatePendingPlans.php`
  - `app/Console/Commands/DeactivateExpiredPlans.php`
- **Schedule:** `routes/console.php`
- **Componente UI:** `app/Livewire/Tenant/Students/AssignPlan.php`
