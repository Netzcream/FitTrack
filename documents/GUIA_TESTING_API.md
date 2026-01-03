# 🧪 Guía de Testing de la API FitTrack

**Para:** Aplicación Multi-Tenant con Laravel  
**Herramientas:** Postman, Thunder Client, o cualquier cliente HTTP

---

## 🎯 Resumen Rápido

Tu API **NO necesita configurar dominios** para funcionar. Usa headers para identificar el tenant.

```
✅ Login: http://localhost/api/auth/login (sin tenant)
✅ Otras rutas: http://localhost/api/... + Header X-Tenant-ID
```

---

## 📋 Pre-requisitos

1. **Servidor corriendo:**
```bash
php artisan serve
# O usa Laragon (ya está en http://localhost)
```

2. **Migraciones ejecutadas:**
```bash
php artisan tenants:migrate
```

3. **Usuario de prueba:**
   - Necesitas un usuario existente en algún tenant
   - Si no tienes, crea uno desde el panel web

---

## 🔐 Paso 1: Login (Obtener Token)

### Request
```http
POST http://localhost/api/auth/login
Content-Type: application/json

{
  "email": "usuario@example.com",
  "password": "password123"
}
```

### Response Esperada (200 OK)
```json
{
  "tenant": {
    "id": "fittrack_client1",
    "name": "Client 1",
    "domain": "http://fittrack_client1.fittrack.test"
  },
  "user": {
    "id": 1,
    "email": "usuario@example.com",
    "name": "Juan Pérez"
  },
  "student": {
    "id": 1,
    "uuid": "abc-123",
    "first_name": "Juan",
    "last_name": "Pérez",
    "email": "usuario@example.com",
    "goal": "hipertrofia",
    "height_cm": 178,
    "weight_kg": 85.5,
    "imc": 27.80
    // ... más campos
  },
  "token": "1|abc123xyz789..."
}
```

### ⚠️ IMPORTANTE: Guarda estos valores

En Postman, crea variables de entorno:
- `{{token}}` = El token de la respuesta
- `{{tenant_id}}` = El tenant.id de la respuesta

---

## 👤 Paso 2: Ver Perfil del Estudiante

### Request
```http
GET http://localhost/api/profile
Authorization: Bearer {{token}}
X-Tenant-ID: {{tenant_id}}
```

### Response Esperada (200 OK)
```json
{
  "data": {
    "id": 1,
    "first_name": "Juan",
    "last_name": "Pérez",
    "full_name": "Juan Pérez",
    "email": "usuario@example.com",
    "phone": "+54 9 11 1234 5678",
    "goal": "hipertrofia",
    "height_cm": 178,
    "weight_kg": 85.5,
    "imc": 27.80
    // ... más campos
  }
}
```

---

## 🏋️ Paso 3: Listar Planes de Entrenamiento

### Request
```http
GET http://localhost/api/plans
Authorization: Bearer {{token}}
X-Tenant-ID: {{tenant_id}}
```

### Response Esperada (200 OK)
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "def-456",
      "name": "Hipertrofia A",
      "description": "Plan de hipertrofia",
      "goal": "hipertrofia",
      "assigned_from": "2026-01-02",
      "assigned_until": "2026-01-30",
      "exercises_count": 12
    }
  ]
}
```

---

## 📅 Paso 4: Ver Plan Actual

### Request
```http
GET http://localhost/api/plans/current
Authorization: Bearer {{token}}
X-Tenant-ID: {{tenant_id}}
```

### Response Esperada (200 OK)
```json
{
  "data": {
    "id": 1,
    "name": "Hipertrofia A",
    "goal": "hipertrofia",
    "assigned_from": "2026-01-02",
    "assigned_until": "2026-01-30",
    "exercises": [
      {
        "id": 1,
        "name": "Press de Banca",
        "day": "Monday",
        "sets": 4,
        "reps": "8-10",
        "weight": 80
      }
    ]
  }
}
```

---

## 💪 Paso 5: Registrar Sesión de Entrenamiento

### Request
```http
POST http://localhost/api/workouts
Authorization: Bearer {{token}}
X-Tenant-ID: {{tenant_id}}
Content-Type: application/json

{
  "training_plan_id": 1,
  "date": "2026-01-02",
  "duration_minutes": 45,
  "status": "completed",
  "rating": 4,
  "notes": "Buen entrenamiento",
  "exercises": [
    {
      "exercise_id": 1,
      "sets_completed": 4,
      "reps_per_set": [10, 10, 8, 8],
      "weight_used_kg": 80,
      "notes": "Última serie con ayuda"
    },
    {
      "exercise_id": 2,
      "sets_completed": 3,
      "reps_per_set": [12, 10, 10],
      "weight_used_kg": 25
    }
  ]
}
```

### Response Esperada (201 Created)
```json
{
  "message": "Sesión de entrenamiento registrada correctamente",
  "data": {
    "id": 1,
    "uuid": "xyz-789",
    "date": "2026-01-02",
    "duration_minutes": 45,
    "status": "completed",
    "rating": 4,
    "exercises_count": 2,
    "training_plan": {
      "id": 1,
      "name": "Hipertrofia A"
    }
  }
}
```

---

## 📊 Paso 6: Listar Sesiones Registradas

### Request
```http
GET http://localhost/api/workouts
Authorization: Bearer {{token}}
X-Tenant-ID: {{tenant_id}}
```

### Response Esperada (200 OK)
```json
{
  "data": [
    {
      "id": 1,
      "date": "2026-01-02",
      "duration_minutes": 45,
      "status": "completed",
      "rating": 4,
      "exercises_count": 2
    }
  ]
}
```

---

## ✏️ Paso 7: Actualizar Perfil

### Request
```http
PATCH http://localhost/api/profile
Authorization: Bearer {{token}}
X-Tenant-ID: {{tenant_id}}
Content-Type: application/json

{
  "weight_kg": 84.0,
  "goal": "definición"
}
```

### Response Esperada (200 OK)
```json
{
  "message": "Perfil actualizado correctamente",
  "data": {
    // datos actualizados
  }
}
```

---

## 🚪 Paso 8: Cerrar Sesión

### Request
```http
POST http://localhost/api/auth/logout
Authorization: Bearer {{token}}
```

### Response Esperada (200 OK)
```json
{
  "message": "Sesión cerrada correctamente"
}
```

---

## ❌ Errores Comunes

### 400 Bad Request - "Tenant ID requerido"
**Causa:** Falta el header `X-Tenant-ID`  
**Solución:** Agregar header con el tenant.id del login

### 401 Unauthorized - "Unauthenticated"
**Causa:** Token inválido, expirado o faltante  
**Solución:** Hacer login nuevamente y usar el nuevo token

### 404 Not Found - "Tenant no encontrado"
**Causa:** El tenant_id no existe  
**Solución:** Verificar con `php artisan tenants:list`

### 404 Not Found - "Perfil de estudiante no encontrado"
**Causa:** El usuario no tiene un perfil de estudiante asociado  
**Solución:** Crear el perfil desde el panel web

### 422 Unprocessable Entity
**Causa:** Datos de validación incorrectos  
**Solución:** Revisar el campo `details` en la respuesta para ver qué falta

---

## 🔧 Configurar Postman Collection

### Variables de Entorno

Crear un entorno llamado "FitTrack Local":

```
base_url = http://localhost
token = (vacío, se llenará después del login)
tenant_id = (vacío, se llenará después del login)
```

### Pre-request Script para Login

Después de hacer login, ejecutar este script para guardar las variables:

```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    pm.environment.set('token', response.token);
    pm.environment.set('tenant_id', response.tenant.id);
}
```

---

## ✅ Checklist de Validación

Una vez que pruebes todos los endpoints, verifica:

- [ ] Login retorna token y datos completos
- [ ] GET /api/profile retorna datos del estudiante
- [ ] GET /api/plans retorna lista de planes
- [ ] GET /api/plans/current retorna plan activo
- [ ] GET /api/plans/{id} retorna ejercicios del plan
- [ ] POST /api/workouts crea sesión correctamente
- [ ] GET /api/workouts retorna historial
- [ ] PATCH /api/profile actualiza datos
- [ ] POST /api/auth/logout revoca token
- [ ] Headers X-Tenant-ID y Authorization funcionan correctamente

---

## 📱 Siguiente Paso: App Móvil

Si todos los tests pasan ✅, tu backend está listo para conectar con la app móvil Expo.

Ver: [PROXIMOS_PASOS.md](./PROXIMOS_PASOS.md) para la implementación de la app.

---

**🎉 ¡Éxito!** Si llegaste hasta aquí, tu API está funcionando correctamente.
